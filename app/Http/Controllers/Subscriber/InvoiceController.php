<?php

namespace App\Http\Controllers\Subscriber;

use App\Models\Currency;
use App\Models\Invoice;
use App\Models\InvoicePurpose;
use App\Lib\HelperTraitSaas as HelperTrait;
use App\Lib\InvoiceApprover;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class InvoiceController extends Controller
{
    use HelperTrait;
    public function __construct(){
        $this->middleware('auth');
    }

    public function index(){
        $invoices = Auth::user()->invoices()->orderBy('id','desc')
                    ->paginate(30);

        return view('subscriber.billing.invoices',[
            'invoices'=>$invoices
        ]);
    }

    public function view(Invoice $invoice){

        $this->authorize('view',$invoice);

        $address = $this->billingAddress();
        return view('subscriber.billing.view-invoice',[
           'invoice'=>$invoice,
            'address'=>$address
        ]);
    }

    public function pay(Invoice $invoice){
        session(['invoice'=>$invoice->id]);
        return redirect()->route('user.invoice.cart');
    }

    public function create(Request $request){
        $this->validate($request,[
           'item_id'=>'required',
            'purpose'=>'required'
        ]);
        $user = Auth::user();
        $itemId = $request->item_id;
        $purpose = $request->purpose;

        if(isset($request->extra)){
            $extra=$request->extra;
        }
        else{
            $extra=null;
        }

        $purposeModel = InvoicePurpose::where('code',$purpose)->firstOrFail();

        $details = $this->getInvoiceItemAmount($purpose,$itemId);
        $amount = $details['amount'];
        //delete any unpaid invoice with the same purpose
        $deleted = Invoice::where('paid',0)
                    ->where('invoice_purpose_id',$purposeModel->id)
                    ->where('user_id',$user->id)
                    ->delete();

        $hash = Hash::make($user->id.$itemId.time());
        $hash = safeUrl($hash);
        $currencyId = session('currency_id');

        if(empty($currencyId)){

            $currencyId = defaultCurrency()->id;
        }

        if (empty($amount)){
            $amount = 0;
        }

        //now create the invoice
        $invoice= Invoice::create([
           'user_id'=>$user->id,
            'invoice_purpose_id'=>$purposeModel->id,
            'amount'=>$amount,
            'paid'=>0,
            'created_date'=>time(),
            'item_id'=>$itemId,
            'extra'=>$extra,
            'auto'=>0,
            'hash'=>$hash,
            'currency_id'=>$currencyId
        ]);



        session()->put('invoice',$invoice->id);
        return redirect()->route('user.invoice.cart');
    }

    public function cart(Request $request){

        $invoiceId = session()->get('invoice');
        $invoice = Invoice::find($invoiceId);

        if($invoice->paid == 1){
            session()->forget('invoice');
            return redirect()->route('user.billing.invoices');
        }


        if($invoice->expires > 0  && $invoice->expires < time()){
            $this->errorMessage(__('saas.expired-invoice'));
            return redirect()->route('user.billing.invoices');
        }
        $description = $invoice->invoicePurpose->purpose;
        $details = $this->getInvoiceItemAmount($invoice->invoicePurpose->code,$invoice->item_id);
        if(!empty($details['description'])){
            $description .= ' - '.$details['description'];
        }

        //get payment methods
        $currencyId = session('currency_id');

        if(empty($currencyId)){

            $currencyId = defaultCurrency()->id;
        }

        $currency = Currency::find($currencyId);


        $paymentMethods = [];
        foreach($currency->paymentMethods()->orderBy('sort_order')->get() as $method){
            $paymentMethods[$method->id] = $method;
        }

        //get global methods
        foreach(PaymentMethod::where('is_global',1)->orderBy('sort_order')->get() as $method){
            $paymentMethods[$method->id] = $method;
        }


        return view('subscriber.billing.cart',['invoice'=>$invoice,'description'=>$description,'paymentMethods'=>$paymentMethods]);
    }

    public function cancel(Request $request)
    {
        session()->forget('invoice');
        $this->successMessage(__('saas.removed-cart'));
        return redirect()->route('user.dashboard');
    }

    public function checkout(Request $request){

        //check if user has any billing address
        $user = Auth::user();
        $address = $this->billingAddress();
        if(!$address){
            return redirect()->route('user.billing-address.create');
        }
        $invoiceId = session()->get('invoice');
        $invoice = Invoice::find($invoiceId);

        if($invoice->paid == 1){
            session()->forget('invoice');
            return redirect()->route('user.billing.invoices');
        }

        if($invoice->expires > 0  && $invoice->expires < time()){
            $this->errorMessage(__('saas.expired-invoice'));
            return redirect()->route('user.billing.invoices');
        }

        //check if it is a free invoice
        if($invoice->amount== 0){
            $result = __('saas.order-success');
            $invoiceApprover = new InvoiceApprover();
            $invoiceApprover->approve($invoiceId);
            session()->forget('invoice');
            $this->successMessage($result);
            return redirect()->route('user.billing.invoices');
        }


        $description = $invoice->invoicePurpose->purpose;
        $details = $this->getInvoiceItemAmount($invoice->invoicePurpose->code,$invoice->item_id);
        if(!empty($details['description'])){
            $description .= ' - '.$details['description'];
        }
        $output= [];
        $output['address']=$address;
        $output['description']=$description;
        $output['user'] = $user;
        $output['item'] = $details;
        $output['invoice'] = $invoice;
        $output['paymentMethod'] = $invoice->paymentMethod->name;

        $currencyId = session('currency_id');

        $view = 'subscriber.methods.'.$invoice->paymentMethod->code;


        return view($view,$output);

    }

    public function selectAddress(){
        $addresses = Auth::user()->billingAddresses;

        return view('subscriber.billing.select-address',['addresses'=>$addresses]);
    }

    public function setAddress($id){
        session()->put('billing_address',$id);
        return redirect()->route('user.invoice.checkout');
    }

    public function complete(){
        $user = Auth::user();

        session()->forget('invoice');

        if(!$user->subscriber->exists()){
            return redirect()->route('user.setup')->with('flash_message',__('saas.payment-complete-msg'));
        }



        return view('subscriber.billing.complete');
    }

    public function setMethod(Request $request){
        $this->validate($request,[
            'method'=>'required|integer'
        ]);
        $requestData = $request->all();
        $invoiceId = session()->get('invoice');
        $invoice = Invoice::find($invoiceId);
        $invoice->payment_method_id = $requestData['method'];
        $invoice->save();
        return redirect()->route('user.invoice.checkout');
    }

    public function callback(Request $request){

        $invoiceId = $request->merchant_order_id;
        $invoice= Invoice::findOrFail($invoiceId);
        $hashSecretWord = config('2co.secret');
        $hashSid = config('2co.id');
        $hashTotal = number_format((float)$invoice->amount, 2, '.', '');
        $hashOrder = $request->order_number;
        $StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $hashOrder . $hashTotal));

        if ($StringToHash != $request->key || $request->credit_card_processed != 'Y' ) {
            $result = 'Payment Failed. Kindly verify your card details.';
            $this->errorMessage($request,$result);
            return redirect()->route('invoice.checkout');

        } else {
            $result = 'Success! Your order was successfully processed.';
            $invoiceApprover = new InvoiceApprover();
            $invoiceApprover->approve($invoiceId);
            session()->forget('invoice');
            $this->successMessage($request,$result);
            return redirect()->route('billing.invoices');
        }

    }


    public function ipn(Request $request){
        $invoiceId = $request->merchant_order_id;
        $invoice= Invoice::findOrFail($invoiceId);
        $hashSecretWord = config('2co.secret');
        $hashSid = config('2co.id');

        $hashTotal = number_format((float)$invoice->amount, 2, '.', '');
        $hashOrder = $request->order_number;
        $StringToHash = strtoupper(md5($hashSecretWord . $hashSid . $hashOrder . $hashTotal));
        if ($StringToHash != $request->key || $request->credit_card_processed != 'Y' ) {
            $result = 'Payment Failed. Kindly verify your card details.';
            return response()->json([
               'status'=>false,
                'message'=>$result
            ]);

        } else {
            $result = 'Success! Your order was successfully processed.';
            $invoiceApprover = new InvoiceApprover();
            $invoiceApprover->approve($invoiceId);
            return response()->json([
                'status'=>true,
                'message'=>$result
            ]);
        }
    }


}
