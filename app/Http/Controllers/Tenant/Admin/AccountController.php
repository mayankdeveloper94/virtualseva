<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Lib\HelperTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Tenant\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Intervention\Image\Facades\Image;
use App\User;
use App\Field;
use Input;
use App\Setting;
use App\Sociallink;
use App\Carouselslider;
use App\Herowidget;
use App\FormField;
use App\FormOption;
use App\Leadform;
use App\Leadformsdata;

class AccountController extends Controller
{
    use HelperTrait;

    public function profile(){
        
        $sociallinks 		= Sociallink::orderBy('id')->paginate(1000);
		
		$group		 		= 'frontsettings';
		
		$frontsettings 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();
		$carouselsliders 	= Carouselslider::orderBy('id')->paginate(1000);	
		
		$group		 		= 'banner';
		
		$banners 	 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();		
		$herowidgets 		= Herowidget::orderBy('id')->paginate(1000);	
		
		$forms 				= FormField::where('field_enabled',1)->orderBy('field_sortorder')->paginate(1000);
		

        $user = Auth::user();
        return view('admin.account.profile',compact('user', 'sociallinks','frontsettings','carouselsliders','banners','herowidgets','forms'));
    }

    public function saveProfile(Request $request){
        $this->validate($request,[
            'name'=>'required',
            'email'=>'required',
            'gender'=>'required',
            'telephone'=>'required',
            'picture' => 'file|max:10000|mimes:jpeg,png,gif',
        ]);

        $requestData = $request->all();
        $user = Auth::user();

        //check for photo
        if($request->hasFile('picture')){
            @unlink($user->picture);

            $path =  $request->file('picture')->store(MEMBERS,'public_uploads');

            $file = 'uploads/'.$path;
            $img = Image::make($file);

            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($file);

            $requestData['picture'] = $file;
        }
        else{
            $requestData['picture'] = $user->picture;
        }

        $user->fill($requestData);
        $user->save();

        return back()->with('flash_message',__('admin.changes-saved'));
    }


    public function password(){
        return view('admin.account.password');
    }

    public function savePassword(Request $request){
        $this->validate($request,[
            'password'=>'required|min:6|confirmed'
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->password);
        $user->save();
        return back()->with('flash_message',__('admin.changes-saved'));
    }

    public function removePicture(){
        $user = Auth::user();
        @unlink($user->picture);
        $user->picture = null;
        $user->save();
        return back()->with('flash_message',__('admin.picture').' '.__('admin.deleted'));
    }
		
    public function kyc(){
        $sociallinks 		= Sociallink::orderBy('id')->paginate(1000);
		
		$group		 		= 'frontsettings';
		
		$frontsettings 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();
		$carouselsliders 	= Carouselslider::orderBy('id')->paginate(1000);	
		
		$group		 		= 'banner';
		
		$banners 	 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();		
		$herowidgets 		= Herowidget::orderBy('id')->paginate(1000);	
		
		$forms 				= FormField::where('field_enabled',1)->orderBy('field_sortorder')->paginate(1000);
		
		if(setting('general_enable_kyc')!=1){
            return redirect()->route('site.departments');
        }
		
		$user = Auth::user();
		
		if($user->role_id == 1){
            return redirect()->route('admin.dashboard');
        }
		
		if($user->is_kyc_verified == 1){
			return redirect()->route('site.departments');
		}
// 		dd($user);
		$member = User::findOrFail($user->id);
        return view('admin.account.kyc',compact('member', 'sociallinks','frontsettings','carouselsliders','banners','herowidgets','forms'));
    }

    public function saveKyc(Request $request){
		
		if(setting('general_enable_kyc')!=1){
            return redirect()->route('site.departments');
        }
		
		$data = $request->all();
		
		$validate_data = array();
		foreach(Field::where('enabled',1)->orderBy('sort_order','asc')->get() as $field){
			if($field->type != "file"){
				$validate_data['field_'.$field->id] = "required";
			} else {
				$validate_data['field_'.$field->id] = "file|max:10000|mimes:jpeg,png,gif";
			}
		}
		
        $this->validate($request,$validate_data);

        $user = Auth::user();
		if($user->is_kyc_verified == 1){
			return redirect()->route('site.departments');
		}
		$member = User::findOrFail($user->id);
		
		$customValues = [];
        //attach custom values
        foreach(Field::where('enabled',1)->orderBy('sort_order','asc')->get() as $field){
			
			if($field->type != "file"){
				if(isset($data['field_'.$field->id]))
				{
					$customValues[$field->id] = ['value'=>$data['field_'.$field->id]];
				}
			} else {	
				if($request->hasFile('field_'.$field->id)){
					@unlink($field->value);
					$file = $request->file('field_'.$field->id);
					$fileName  = md5(uniqid()) . '.' . $file->getClientOriginalExtension();
					$destinationPath = public_path('uploads/'.MEMBERS.'/kyc');
					$store_file = 'uploads/'.MEMBERS.'/kyc/'.$fileName;
					$file->move($destinationPath, $fileName);
					$customValues[$field->id] = ['value'=>$store_file];
				}
			}
			
        }
		
        $member->fields()->sync($customValues);		
		$member->is_kyc_updated = 1;
		$member->save();
        return redirect()->route('site.departments')->with('flash_message',__('admin.changes-saved'));
    }

	
}
