<?php
/**
 * Created by PhpStorm.
 * User: USER PC
 * Date: 4/27/2017
 * Time: 1:41 PM
 */

namespace App\Lib;



use App\Mail\Generic;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

trait HelperTrait {

    public function successMessage($request,$message){
        $request->session()->flash('alert-success', $message);
    }


    public function warningMessage($request,$message){
        $request->session()->flash('alert-warning', $message);
    }


    public function errorMessage($request,$message){
        $request->session()->flash('alert-danger', $message);
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


    public function sendSMS($reciepientNo,$message){

        return true;

    }



    public function notifyAdmins($subject,$message){

        $users = User::where('role_id',1)->get();
        foreach ($users as $user){
            $email = $user->email;
            // Always set content-type when sending HTML email
         /*   $headers = "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";


            $headers .= 'From: '.config('mail.from')['name'].'<'.config('mail.from')['address'].'>' . "\r\n";*/

            $this->sendEmail($email,$subject,$message);
        }
    }

    public function loginToDepartment($id){
        session()->put('department',$id);
    }
	
	

}