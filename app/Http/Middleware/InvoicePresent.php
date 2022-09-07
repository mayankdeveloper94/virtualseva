<?php

namespace App\Http\Middleware;

use App\Models\Invoice;
use Closure;

class InvoicePresent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $invoiceId = session()->get('invoice');

        if(empty($invoiceId)){
            return redirect()->route('user.plans');
        }

        //check if invoice has been paid for
        $invoice = Invoice::find($invoiceId);

        if($invoice){
            if($invoice->paid==1){
                session()->forget('invoice');
                return redirect()->route('user.billing.invoices');

            }
        }
        else{
            session()->forget('invoice');
        }


        return $next($request);
    }
}
