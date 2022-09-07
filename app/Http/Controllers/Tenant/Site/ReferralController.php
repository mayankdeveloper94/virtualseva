<?php

namespace App\Http\Controllers\Tenant\Site;

use App\User;
use App\RefUser;
use App\Lib\HelperTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Tenant\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;

use App\Setting;
use App\Sociallink;
use App\Carouselslider;
use App\Herowidget;
use App\FormField;
use App\FormOption;
use App\Leadform;
use App\Leadformsdata;
use Response;

use Illuminate\Support\Str;

class ReferralController extends Controller
{
    use HelperTrait;

    public function myReferral(){
        $sociallinks 		= Sociallink::orderBy('id')->paginate(1000);
		
		$group		 		= 'frontsettings';
		
		$frontsettings 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();
		$carouselsliders 	= Carouselslider::orderBy('id')->paginate(1000);	
		
		$group		 		= 'banner';
		
		$banners 	 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();		
		$herowidgets 		= Herowidget::orderBy('id')->paginate(1000);	
		
		$forms 				= FormField::where('field_enabled',1)->orderBy('field_sortorder')->paginate(1000);
		
		if(setting('general_enable_referral')!=1){
			return redirect()->route('site.departments');
		}
        $user = Auth::user();
		if(!$user->ref){
			abort(404,'Page not found');
		}
		$refCount = RefUser::where('ref_code',$user->ref)->count();
        return view('site.referral.my-referral',compact('refCount','user', 'sociallinks','frontsettings','carouselsliders','banners','herowidgets','forms'));
    }

    public function refnew($code){
		if(setting('general_enable_referral')!=1){
			return redirect()->route('site.departments');
		}
		//$minutes = 1440;
		//Cookie::queue('ref', $code, $minutes);
		//return redirect()->route('register');
		//$RefUser = RefUser::where('ref_code',$code)->first();
		
		//print_r($RefUser);die;
		
		//$user = User::find($RefUser->ref_user_id);
		$user = User::where('ref',$code)->first();
		if(!$user){
			abort(404,'Page not found');
		}
		return view('site.referral.my-referral-public',compact('user'));
    }
	
	public function ref($code){
		if(setting('general_enable_referral')!=1){
			return redirect()->route('site.departments');
		}
		
		$user = User::where('ref',$code)->first();
		if(!$user){
			abort(404,'Page not found');
		}
		
		/*		
		$requestDataIns = array("_token" => csrf_token(),"formrefid" => "AAS48287K");
		$leadform 		= Leadform::create($requestDataIns);	
				
		$requestDataIns1 = array("_token" => csrf_token(),"leadform_id" => $leadform->id, "form_field_id" => "6", "form_field_value" => "test 1");
		$leadformsdata 	 = Leadformsdata::create($requestDataIns1);
		*/
		
		$sociallinks 		= Sociallink::orderBy('id')->paginate(1000);
		
		$group		 		= 'frontsettings';
		
		$frontsettings 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();
		$carouselsliders 	= Carouselslider::orderBy('id')->paginate(1000);	
		
		$group		 		= 'banner';
		
		$banners 	 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();		
		$herowidgets 		= Herowidget::orderBy('id')->paginate(1000);	
		
		$forms 				= FormField::where('field_enabled',1)->orderBy('field_sortorder')->paginate(1000);
		
		return view('site.referral.my-referral-public-new',compact('user','sociallinks','frontsettings','carouselsliders','banners','herowidgets','forms','code'));
    }

	public function leadformsubmit($code, Request $request){
		
		$requestDataIns = array("_token" => csrf_token(),"formrefid" => $code);
		$leadform 		= Leadform::create($requestDataIns);	
		$leadform_id	= $leadform->id;
		
		
		
		$requestData 	= $request->all();
		$requestDataarr = array();
		
		foreach ($requestData as $requestDataitem => $requestDatavalue) {
			
			
			if(is_numeric($requestDataitem)){
				$requestDataarr[] = array("form_field_id" => $requestDataitem, "form_field_value" => $requestDatavalue);
			}
			
		}	
		
		
		
		for($i=0;$i<sizeof($requestDataarr);$i++){
			
			$requestDataIns1 = array("_token" => csrf_token(),"leadform_id" => $leadform_id, "form_field_id" => $requestDataarr[$i]["form_field_id"], "form_field_value" => $requestDataarr[$i]["form_field_value"]);
			
			$leadformsdata 	 = Leadformsdata::create($requestDataIns1);
			
		}	
		
		
		
		return Response::json(array('success' => '1'));
		
	}	
	
	public function leadformsubmitNew(Request $request){
		
		$requestDataIns = array("_token" => csrf_token(),"formrefid" => Str::random(20));
		$leadform 		= Leadform::create($requestDataIns);	
		$leadform_id	= $leadform->id;
		
		
		
		$requestData 	= $request->all();
		$requestDataarr = array();
		
		foreach ($requestData as $requestDataitem => $requestDatavalue) {
			
			
			if(is_numeric($requestDataitem)){
				$requestDataarr[] = array("form_field_id" => $requestDataitem, "form_field_value" => $requestDatavalue);
			}
			
		}	
		
		
		
		for($i=0;$i<sizeof($requestDataarr);$i++){
			
			$requestDataIns1 = array("_token" => csrf_token(),"leadform_id" => $leadform_id, "form_field_id" => $requestDataarr[$i]["form_field_id"], "form_field_value" => $requestDataarr[$i]["form_field_value"]);
			
			$leadformsdata 	 = Leadformsdata::create($requestDataIns1);
			
		}	
		
		
		
		return Response::json(array('success' => '1'));
		
	}

}
