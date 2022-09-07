<?php
/**
 * Created by PhpStorm.
 * User: USER PC
 * Date: 3/23/2018
 * Time: 11:34 AM
 */

namespace App\Lib;


use Application\Model\SettingTable;
use Application\Model\SmsGatewayFieldTable;
use Application\Model\SmsGatewayTable;

class SmsGateway {


    private $recipients;
    private $message;
    private $smsEnabled;

    public function __construct($recipients,$message){

        $this->recipients = $recipients;
        $this->message = $message;
        $this->smsEnabled= setting('sms_enabled')==1?true:false;
    }



    public function send(){


        $activeGateway = \App\SmsGateway::where('active',1)->first();

        if(!$activeGateway){
            return __('admin.sms-sending-failed');
        }

        if(!$this->smsEnabled){
            return __('admin.sms-failed-disabled');
        }


        if(empty($this->recipients)){
            return __('admin.sms-failed-no-recp');
        }

        $code= $activeGateway->code;
        try{
            return call_user_func([$this,$code]);
        }
        catch(\Exception $ex){
            return $ex->getMessage();
        }


    }


    public function smartsms(){


        $code='smartsms';
        $gateway = \App\SmsGateway::where('code',$code)->first();
        $apiGetEndpoint = 'http://api.smartsmssolutions.com/smsapi.php';
        $smsUsername = $gateway->smsGatewayFields()->where('key','username')->first()->value;//$this->smsGatewayFieldTable->getField($gatewayid,'username');
        $smsPassword  = $gateway->smsGatewayFields()->where('key','password')->first()->value;
        $senderName =  $gateway->smsGatewayFields()->where('key','sender_name')->first()->value;

        $pendingNumberlist = '';
        if(is_array($this->recipients)){
            foreach($this->recipients as $value){
                $pendingNumberlist .= $value.',';
            }

        }
        else{
            $pendingNumberlist = $this->recipients;
        }
        $smsArray = [
            'username'=>$smsUsername,
            'password'=>$smsPassword,
            'message'=>$this->message,
            'sender'=>$senderName,
            'recipient'=>$pendingNumberlist
        ];

        $data = $this->sendsms_post($apiGetEndpoint,$smsArray);
        return $data;
    }


    public function cheapglobal(){

        $code = 'cheapglobal';
        $gateway = \App\SmsGateway::where('code',$code)->first();
        $pendingNumberlist = '';
        if(is_array($this->recipients)){
            foreach($this->recipients as $value){
                $pendingNumberlist .= $value.',';
            }

        }
        else{
            $pendingNumberlist = $this->recipients;
        }

        $post_data=array(
            'sub_account'=> $gateway->smsGatewayFields()->where('key','sub_account')->first()->value,
            'sub_account_pass'=> $gateway->smsGatewayFields()->where('key','sub_account_pass')->first()->value,
            'action'=>'send_sms',
            'sender_id'=>$gateway->smsGatewayFields()->where('key','sender_name')->first()->value,
            'recipients'=>$pendingNumberlist,
            'message'=>$this->message
        );

        $api_url='http://cheapglobalsms.com/api_v1';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        $response_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($response_code != 200)$response=curl_error($ch);
        curl_close($ch);

        if($response_code != 200)$msg="HTTP ERROR $response_code: $response";
        else
        {
            $json=@json_decode($response,true);

            if($json===null)$msg="INVALID RESPONSE: $response";
            elseif(!empty($json['error']))$msg=$json['error'];
            else
            {
                $msg = __('admin.sms-sent-msg',['total'=>$json['total']]);
                $sms_batch_id=$json['batch_id'];
            }
        }

        return $msg;

    }



    public function clickatell(){
        $code = 'clickatell';
        $gateway = \App\SmsGateway::where('code',$code)->first();


        $apiKey =$gateway->smsGatewayFields()->where('key','api_key')->first()->value;
        $numbers = [];

        if(is_array($this->recipients)){
            $numbers = $this->recipients;
        }
        else{
            $numbers[] = $this->recipients;
        }
        $count = 0;
        foreach($numbers as $key=>$value){
            $number = str_ireplace('+','',$value);
            $url =  'https://platform.clickatell.com/messages/http/send?apiKey='.urlencode($apiKey).'&to='.$number.'&content='.urlencode($this->message);
            file_get_contents($url);
            $count++;
        }

        return $count;

    }


    public function bulksms(){
        $code = 'bulksms';
        $gateway = \App\SmsGateway::where('code',$code)->first();


        $username = trim($gateway->smsGatewayFields()->where('key','username')->first()->value);
        $password = trim($gateway->smsGatewayFields()->where('key','password')->first()->value);


        $messages = [];

        $numbers = [];

        if(is_array($this->recipients)){
            $numbers = $this->recipients;
        }
        else{
            $numbers[] = $this->recipients;
        }

        foreach($numbers as $value){
            $messages[]= [
                'to'=>$value,
                'body'=>$this->message
            ];
        }

        $result = $this->send_message( json_encode($messages), 'https://api.bulksms.com/v1/messages?auto-unicode=true&longMessageMaxParts=30', $username, $password );

        if ($result['http_status'] != 201) {
            return "Error sending: " . ($result['error'] ? $result['error'] : "HTTP status ".$result['http_status']."; Response was " .$result['server_response']);
        } else {
            return  $result['server_response'];
            // Use json_decode($result['server_response']) to work with the response further
        }


    }







//--------------------Helpers---------------------------//
    private function send_message ( $post_body, $url, $username, $password) {
        $ch = curl_init( );
        $headers = array(
            'Content-Type:application/json',
            'Authorization:Basic '. base64_encode("$username:$password")
        );
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt ( $ch, CURLOPT_URL, $url );
        curl_setopt ( $ch, CURLOPT_POST, 1 );
        curl_setopt ( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt ( $ch, CURLOPT_POSTFIELDS, $post_body );
        // Allow cUrl functions 20 seconds to execute
        curl_setopt ( $ch, CURLOPT_TIMEOUT, 20 );
        // Wait 10 seconds while trying to connect
        curl_setopt ( $ch, CURLOPT_CONNECTTIMEOUT, 10 );
        $output = array();
        $output['server_response'] = curl_exec( $ch );
        $curl_info = curl_getinfo( $ch );
        $output['http_status'] = $curl_info[ 'http_code' ];
        $output['error'] = curl_error($ch);
        curl_close( $ch );
        return $output;
    }

    private function sendsms_post ($url, array $params) {     $params = http_build_query($params);     $ch = curl_init();           curl_setopt($ch, CURLOPT_URL,$url);     curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);     curl_setopt($ch, CURLOPT_POST, 1);     curl_setopt($ch, CURLOPT_POSTFIELDS, $params);          $output=curl_exec($ch);       curl_close($ch);     return $output;         }

}