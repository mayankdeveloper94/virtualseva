<?php
namespace App\LibSaas;


use App\MailSaas\Generic;
use App\Models\Invoice;
use App\Models\PackageDuration;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

trait HelperTraitSaas {

    public function successMessage($message){
        request()->session()->flash('alert-success', $message);
    }


    public function warningMessage($message){
        request()->session()->flash('alert-warning', $message);
    }


    public function errorMessage($message){
        request()->session()->flash('alert-danger', $message);
    }

    public function getInvoiceItemAmount($code,$itemId){
        switch($code){
            case 'subscription':
                $packageDuration = PackageDuration::find($itemId);
                $duration = ($packageDuration->type=='m') ? __('saas.monthly'):__('saas.yearly');
                return [
                    'amount'=>$packageDuration->price,
                    'description'=>$packageDuration->package->name. ' ('.$duration.')',
                    'record'=>$packageDuration
                ];
                break;
        }
    }

    public function billingAddress(){
        $user = Auth::user();
        $addresses = $user->billingAddresses()->count();
        if(empty($addresses)){
            return false;
        }
        elseif(session('billing_address')){
            $addressId = session('billing_address');
            try{
                $address = $user->billingAddresses()->where('id',$addressId)->firstOrFail();
                return $address;
            }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $ex){

                $address = $user->billingAddresses()->first();
                return $address;
            }
        }
        else{
            //get the default address
            try{
                $address = $user->billingAddresses()->where('is_default',1)->firstOrFail();
                return $address;
            }catch(\Illuminate\Database\Eloquent\ModelNotFoundException $ex){
                $address = $user->billingAddresses()->first();
                return $address;
            }

        }
    }

    public function addSeconds($seconds,$invoiceId){

        $invoice = Invoice::findOrFail($invoiceId);
        $user = $invoice->user;
        //get package duration
        $packageDuration = PackageDuration::findOrFail($invoice->item_id);

        if(!$user->subscriber()->exists()){
            return true;
        }

        if($user->subscriber->expires > time() && $packageDuration->package->id == $user->subscriber->packageDuration->package->id ){
            $oldSeconds = $user->subscriber->expires;
        }
        else{
            $oldSeconds = time();
        }



        $newSeconds = $oldSeconds + $seconds;
        $user->subscriber->expires = $newSeconds;
        $user->subscriber->package_duration_id = $invoice->item_id;
        $user->trial = 0;
        $user->subscriber->save();
        $user->save();
    }


    public function getInvoiceItemName($invoiceId){

        $invoice = Invoice::find($invoiceId);
        if(!$invoice){
            return '';
        }

        if($invoice->invoicePurpose->id==1){
            $packageDuration = PackageDuration::find($invoice->item_id);
            if(!$packageDuration){
                return '';
            }

            if($packageDuration->type=='m'){
                $duration = __('saas.monthly');
            }
            else{
                $duration = __('saas.annual');
            }

            $name = $packageDuration->package->name." ({$duration})";
            return $name;
        }
        else{
            return '';
        }


    }

    public function sendEmail($recipientEmail,$subject,$message,$from=null,$cc=null){

        $cc = $this->extract_emails($cc);

        if(!empty($cc)){

            //generate array from cc
            $ccArray = explode(',',$cc);
            $allCC = [];
            foreach($ccArray as $key=>$value){
                $value = trim($value);
                $validator = Validator::make(['email'=>$value],['email'=>'email']);

                if(!$validator->fails()){
                    $allCC[] = $value;
                }

            }

            Mail::to($recipientEmail)->cc($allCC)->send(New Generic($subject,$message,$from));
        }
        else{
            Mail::to($recipientEmail)->send(New Generic($subject,$message,$from));
        }

    }

    private  function extract_emails($str){
        // This regular expression extracts all emails from a string:
        $regexp = '/([a-z0-9_\.\-])+\@(([a-z0-9\-])+\.)+([a-z0-9]{2,4})+/i';
        preg_match_all($regexp, $str, $m);

        $emails= isset($m[0]) ? $m[0] : array();
        $newEmails = [];
        foreach($emails as $key=>$value){
            $newEmails[$value] = $value;
        }

        if(count($newEmails)>0){
            $addresses = implode(' , ',$newEmails);
            return $addresses;
        }
        else{
            return null;
        }



    }


}