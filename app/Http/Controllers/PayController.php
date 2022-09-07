<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class PayController extends Controller
{
    //

    public function pay($hash){

        $hash = trim($hash);
        //check for invoice
        if(Invoice::where('hash',$hash)->where('paid',0)->count()>0){
           $invoice=  Invoice::where('hash',$hash)->where('paid',0)->first();
           Auth::login($invoice->user);
            session()->put('invoice',$invoice->id);
            return redirect()->route('user.invoice.cart');
        }
        else{
            return redirect()->route('homepage');
        }

    }

    public function currency($currency){

        $currencyModel = Currency::findOrFail($currency);

        Session::put('currency_id',$currency);
        if(Auth::check()){
            $user = Auth::user();

            if($user->subscriber()->exists()){
                $user->subscriber->currency_id = $currency;
                $user->subscriber->save();

                $invoiceId = session()->get('invoice');
                if(!empty($invoiceId)){
                    $invoice = Invoice::find($invoiceId);
                    $invoice->currency_id = $currency;
                    $invoice->save();
                }
            }

        }
        return back();
    }

}
