<?php

namespace App\Http\Controllers\Subscriber;

use App\Lib\HelperTraitSaas;
use App\Lib\InvoiceApprover;
use App\Models\Invoice;
use App\Models\PackageDuration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PayPal\Api\VerifyWebhookSignature;
use PayPal\Api\WebhookEvent;


class PaypalController extends Controller
{
    use HelperTraitSaas;

    public function setup(){
        $invoiceId = session()->get('invoice');
        $invoice = Invoice::find($invoiceId);
        if(!$invoice){
            return back();
        }

        $paypal = getPaypalClient();

        $plan = new \PayPal\Api\Plan();

        $description = $invoice->invoicePurpose->purpose;
        $details = $this->getInvoiceItemAmount($invoice->invoicePurpose->code,$invoice->item_id);
        if(!empty($details['description'])){
            $description .= ' - '.$details['description'].' - '.price($details['amount'],$invoice->currency_id);
        }

        $plan->setName($invoice->invoicePurpose->purpose)
            ->setDescription($description)
            ->setType('INFINITE');

        $amount = priceRaw($invoice->amount,$invoice->currency_id);
        $currencyCode = strtoupper($invoice->currency->country->currency_code);

        //get frequency
        $packageDuration = PackageDuration::find($invoice->item_id);
        if($packageDuration->type=='a'){
            $freq='Year';
        }
        else{
            $freq='Month';
        }

        $paymentDefinition = new \PayPal\Api\PaymentDefinition();
        $paymentDefinition->setName('Regular Payments')
            ->setType('REGULAR')
            ->setFrequency($freq)
            ->setFrequencyInterval("1")
            ->setAmount(new \PayPal\Api\Currency(array('value' => $amount, 'currency' => $currencyCode)));

        $merchantPreferences = new \PayPal\Api\MerchantPreferences();

        $merchantPreferences->setReturnUrl(route('user.paypal.callback'))
            ->setCancelUrl(route('user.invoice.cart'))
            ->setAutoBillAmount("yes")
            ->setInitialFailAmountAction("CONTINUE")
            ->setMaxFailAttempts("0")
            ->setSetupFee(new \PayPal\Api\Currency(array('value' => $amount, 'currency' => $currencyCode)));

        $plan->setPaymentDefinitions(array($paymentDefinition));
        $plan->setMerchantPreferences($merchantPreferences);


        try {
            $createdPlan = $plan->create($paypal);
        } catch (\Exception $ex) {
            $msg = $ex->getMessage();
            return back()->with('flash_message',$msg);
        }

        try {
            $patch = new \PayPal\Api\Patch();

            $value = new \PayPal\Common\PayPalModel('{
               "state":"ACTIVE"
                }');

            $patch->setOp('replace')
                ->setPath('/')
                ->setValue($value);
            $patchRequest = new \PayPal\Api\PatchRequest();
            $patchRequest->addPatch($patch);

            $createdPlan->update($patchRequest, $paypal);

            $createdPlan = \PayPal\Api\Plan::get($createdPlan->getId(), $paypal);
        } catch (\Exception $ex) {
            $msg = $ex->getMessage();
            return back()->with('flash_message',$msg);
        }


        $agreement = new \PayPal\Api\Agreement();

        $agreement->setName( $invoice->invoicePurpose->purpose)
            ->setDescription($description)
            // set the start date to 1 month from now as we take our first payment via the setup fee
            ->setStartDate(gmdate("Y-m-d\TH:i:s\Z", strtotime("+1 ".strtolower($freq), time())));

// Link the plan up with the agreement
        $plan = new \PayPal\Api\Plan();
        $plan->setId($createdPlan->getId());
        $agreement->setPlan($plan);

        $payer = new \PayPal\Api\Payer();
        $payer->setPaymentMethod('paypal');
        $agreement->setPayer($payer);


        try {
            // Please note that as the agreement has not yet activated, we wont be receiving the ID just yet.
            $agreement = $agreement->create($paypal);

            // Get redirect url
            $approvalUrl = $agreement->getApprovalLink();
        } catch (\Exception $ex) {
            $msg = $ex->getMessage();
            return back()->with('flash_message',$msg);
        }

        return redirect($approvalUrl);

    }

    public function callback(Request $request){
        $token = $request->token;
        if(empty($token)){
            return redirect()->route('user.invoice.cart');
        }

        $invoiceId = session()->get('invoice');
        $invoice = Invoice::find($invoiceId);
        if(!$invoice){
            return back();
        }

        $paypal = getPaypalClient();

        $agreement = new \PayPal\Api\Agreement();
        try {
            // Execute the agreement by passing in the token
            $agreement->execute($token, $paypal);
        } catch (\Exception $ex) {
            return redirect()->route('user.invoice.cart')->with('flash_message',$ex->getMessage());
        }

        $invoice->paypal_id = $agreement->getId();
        $invoice->save();

        $approver = new InvoiceApprover();
        $approver->approve($invoiceId,true);

        return redirect()->route('user.invoice.payment-complete');

    }

    public function webhook(Request $request){
        $apiContext = getPaypalClient();

        /**
         * Receive the entire body that you received from PayPal webhook.
         */
        $bodyReceived = file_get_contents('php://input');

        /**
         * Receive HTTP headers that you received from PayPal webhook.
         */
        $headers = getallheaders();

        /**
         * Uppercase all the headers for consistency
         */
        $headers = array_change_key_case($headers, CASE_UPPER);

        $webhookId = paymentSetting(1,'webhook_id');

        $signatureVerification = new VerifyWebhookSignature();
        $signatureVerification->setWebhookId($webhookId);
        $signatureVerification->setAuthAlgo($headers['PAYPAL-AUTH-ALGO']);
        $signatureVerification->setTransmissionId($headers['PAYPAL-TRANSMISSION-ID']);
        $signatureVerification->setCertUrl($headers['PAYPAL-CERT-URL']);
        $signatureVerification->setTransmissionSig($headers['PAYPAL-TRANSMISSION-SIG']);
        $signatureVerification->setTransmissionTime($headers['PAYPAL-TRANSMISSION-TIME']);

        $webhookEvent = new WebhookEvent();
        $webhookEvent->fromJson($bodyReceived);
        $signatureVerification->setWebhookEvent($webhookEvent);
        $request = clone $signatureVerification;

        try {
            /** @var \PayPal\Api\VerifyWebhookSignatureResponse $output */
            $output = $signatureVerification->post($apiContext);
        } catch (\Exception $ex) {
            print_r($ex->getMessage());
            exit(1);
        }


        $verificationStatus = $output->getVerificationStatus();
        $responseArray = json_decode($request->toJSON(), true);

        $event = $responseArray['webhook_event']['resource']['event_type'];

        $outputArray = json_decode($output->toJSON(), true);

        if ($verificationStatus == 'SUCCESS') {
            switch($event) {
                case 'BILLING.SUBSCRIPTION.CANCELLED':
                case 'BILLING.SUBSCRIPTION.SUSPENDED':
                    // subscription canceled: agreement id = $responseArray['webhook_event']['resource']['id']
                    break;
                case 'PAYMENT.SALE.COMPLETED':
                    //subscription payment recieved: agreement id = $responseArray['webhook_event']['resource']['billing_agreement_id']
                    $agreementId = $responseArray['webhook_event']['resource']['billing_agreement_id'];
                    $invoice = Invoice::where('paypal_id',$agreementId)->first();
                    if($invoice){
                        if(!empty($invoice->expires) && $invoice->expires < time()){
                            http_response_code(400);
                            exit();
                        }

                        if($invoice->paid==1){
                            $invoice = $invoice->replicate();
                            $invoice->paid = 0;
                            $invoice->save();
                        }

                        $approver= new InvoiceApprover();
                        $approver->approve($invoice->id,true);

                    }
                    break;
            }
        }


    }

}
