<?php

namespace App\Http\Controllers\Admin;

use App\Models\PackageDuration;
use App\Models\Setting;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;

class SettingsController extends Controller
{


    public function settings($group){
        $settings = Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();

        return view('admin.settings.settings',compact('settings','group'));
    }



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

                    $path =  $request->file($key)->store('settings','saas_uploads');

                    $file = 'saas_uploads/'.$path;


                    $img = Image::make($file);

                    $img->resize(500, null, function ($constraint) {
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
           //     continue;
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


    public function trial(){

        $packages = PackageDuration::get();
        return view('admin.settings.trial',compact('packages'));
    }

    public function saveTrial(Request $request){

        $rules = [
            'trial_enabled'=>'required'
        ];

        if($request->trial_enabled==1){
            $rules['trial_package_duration_id']='required';
            $rules['trial_days']='required';
        }

        $this->validate($request,$rules);

        Setting::where('key','trial_enabled')->update(['value'=>$request->trial_enabled]);
        Setting::where('key','trial_package_duration_id')->update(['value'=>$request->trial_package_duration_id]);
        Setting::where('key','trial_days')->update(['value'=>$request->trial_days]);
        return back()->with('flash_message',__('admin.changes-saved'));
    }


    public function profile(){

        $user= Auth::user();
        return view('admin.settings.profile',compact('user'));
    }

    public function saveProfile(Request $request){

        $requestData = $request->all();
        $user = Auth::user();
        $rules = [
            'name'=>'required',
            'email'=>'required|email'
        ];

        if($requestData['email']!=$user->email){
            $rules['email'] = 'required|email|unique:users';
        }

        $this->validate($request,$rules);

        if(!empty($requestData['password'])){
            $requestData['password']= Hash::make($requestData['password']);
        }
        else{
            $requestData['password'] = $user->password;
        }

        $user->fill($requestData);
        $user->save();

        return back()->with('flash_message',__('admin.changes-saved'));
    }


}
