<?php

namespace App\Http\Controllers\Subscriber;

use App\Lib\InvoiceApprover;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class WebhooksController extends Controller
{
    public function stripe(Request $request){
        \Stripe\Stripe::setApiKey(paymentSetting(2,'secret_key'));

// You can find your endpoint's secret in your webhook settings
        $endpoint_secret = paymentSetting(2,'endpoint_secret');

        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400);
            exit();
        } catch(\Stripe\Error\SignatureVerification $e) {
            // Invalid signature
            http_response_code(400);
            exit();
        }

// Handle the checkout.session.completed event
        if ($event->type == 'checkout.session.completed') {
            $session = $event->data->object;

            // Fulfill the purchase...
            $invoiceId = $session->client_reference_id;
            $invoice = Invoice::find($invoiceId);
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
            else{
                http_response_code(400);
                exit();
            }
        }

        http_response_code(200);
    }

    public function twocheckout(){

    }
}
