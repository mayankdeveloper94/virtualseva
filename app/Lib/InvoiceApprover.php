<?php
/**
 * Created by PhpStorm.
 * User: USER PC
 * Date: 5/2/2017
 * Time: 11:15 AM
 */

namespace App\Lib;



use App\Models\Invoice;
use App\Models\PackageDuration;

class InvoiceApprover {

    use HelperTraitSaas;

    public function approve($invoiceId,$autoRenew=false){

        $invoice = Invoice::findOrFail($invoiceId);
        $code = $invoice->invoicePurpose->code;
        $itemId = $invoice->item_id;

        if($invoice->paid==1){
            return true;
        }

        switch($code){
            case 'subscription':
                //get the selected package
                $packageDuration = PackageDuration::findOrFail($itemId);
                $this->addSeconds($packageDuration->seconds,$invoiceId);
                break;
        }

        $invoice->paid = 1;
        $invoice->save();

        $user = $invoice->user;
        $user->trial = 0;
        $user->save();

        if($user->subscriber()->exists()){
            if($autoRenew){
                $user->subscriber->auto_renew = 1;
                $user->subscriber->save();
            }else{
                $user->subscriber->auto_renew = 0;
                $user->subscriber->save();
            }
        }
        session()->forget('invoice');
    }

}