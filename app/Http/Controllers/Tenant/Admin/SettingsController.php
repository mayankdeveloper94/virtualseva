<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Setting;
use App\SmsGateway;
use Illuminate\Http\Request;
use App\Http\Controllers\Tenant\Controller;
use Intervention\Image\Facades\Image;

class SettingsController extends Controller
{


    public function settings($group){
        
				
		$settings = Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();

        return view('admin.settings.settings',compact('settings','group'));
    }


 /*   public function saveSettings(Request $request){

        $requestData = $request->all();

        $key='image_logo';

        $files= ['image_logo'=>''];



        $files = $request->files->all();

        if(!empty($files)){
            foreach($files as $key=>$value) {

                if ($request->hasFile($key)) {

                    $setting = Setting::where('key', $key)->first();
                    if (!$setting) {
                        //  continue;
                    }

                    @unlink($setting->value);

                    $path = $request->file($key)->store(SETTINGS, 'public_uploads');

                    $file = 'uploads/' . $path;
                    $img = Image::make($file);

                    $img->resize(500, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $img->save($file);

                    $setting->value = trim($file);
                    $setting->save();
                }


            }}
        echo 'done';

    }*/

    public function saveSettings(Request $request){

        $requestData = $request->all();



        $files = $request->files->all();



        if(!empty($files)){
            $rules = [];

            foreach($files as $key=>$value){
                $rules[$key]='file|max:10000|mimes:jpeg,png,gif';
            }
            $this->validate($request,$rules);
        }




        if(!empty($files)){
            foreach($files as $key=>$value){
                if($request->hasFile($key)){

                    $setting= Setting::where('key',$key)->first();
                    if(!$setting){
                        continue;
                    }

                    @unlink($setting->value);

                    $path =  $request->file($key)->store(SETTINGS,'public_uploads');

                    $file = 'uploads/'.$path;


                    $img = Image::make($file);

                    $img->resize(1440, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                    });
                    $img->save($file);


                    $setting->value = trim($file);
                    $setting->save();


                }
            }
        }


        foreach($requestData as $key=>$value){

            if(!is_string($value)){
                continue;
            }

            $setting= Setting::where('key',$key)->first();
            if($setting){
                $setting->value = trim($value);
                $setting->save();
            }

        }

        return back()->with('flash_message',__('admin.changes-saved'));
    }

    public function removePicture(Setting $setting){


        @unlink($setting->value);
        $setting->value = null;
        $setting->save();
        return back()->with('flash_message',__('admin.picture-removed'));
    }

    public function smsGateways()
    {
        $smsSetting = setting('sms_enabled');
        $activeGateway = SmsGateway::where('active',1)->first();

        //get all gateways
        $smsGateways = SmsGateway::orderBy('gateway_name')->paginate(30);

        return view('admin.settings.sms_gateway',compact('smsSetting','activeGateway','smsGateways'));
    }


    public function saveSmsSetting(Request $request){

     $this->validate($request,[
         'sms_max_pages'=>'required'
     ]);

        Setting::where('key','sms_max_pages')->update(['value'=>$request->sms_max_pages]);
        return back()->with('flash_message',__('admin.changes-saved'));

    }

    public function smsFields(SmsGateway $smsGateway){


        return view('admin.settings.sms_fields',compact('smsGateway'));
    }

    public function saveField(Request $request,SmsGateway $smsGateway){

        $requestData = $request->all();

        foreach($smsGateway->smsGatewayFields as $field){
            $field->value = $requestData[$field->key];
            $field->save();
        }

        return back()->with('flash_message',__('admin.changes-saved'));

    }

    public function setSmsStatus(SmsGateway $smsGateway,$status){

        SmsGateway::where('id','>',0)->update(['active'=>0]);

        $smsGateway->active= $status;
        $smsGateway->save();
        return back()->with('flash_message',__('admin.changes-saved'));
    }

    public function language(){
        $languages = ['en'];
        $others = config('auto-translate.target_language');

        $languages = array_merge($languages,$others);
        sort($languages);
        $controller = $this;
        return view('admin.settings.language',compact('languages','controller'));

    }

    public function saveLanguage(Request $request){
        $this->validate($request,[
            'config_language'=>'required'
        ]);

        Setting::where('key','config_language')->update(['value'=>$request->config_language]);
        return back()->with('flash_message',__('admin.changes-saved'));
    }

    public function languageName($code){

        $lib = config('auto-translate.dict');
        return $lib[$code];
    }
	
	public function form(){
	
		echo "Run ...";
		exit(0);
		
	}	
	
	
}
