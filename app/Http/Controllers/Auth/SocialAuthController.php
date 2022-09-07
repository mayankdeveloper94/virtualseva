<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;

use App\Http\Requests;
use Response;
use Socialite;
use App\Models\User;
use Auth;
use Session;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;


class SocialAuthController extends Controller
{
	
    public function redirect($provider, Request $request)
    {		
		return Socialite::driver($provider)->redirect();				
    }
	
	public function Callback($provider){
        $userSocial 	=   Socialite::driver($provider)->stateless()->user();
        $users       	=   User::where(['email' => $userSocial->getEmail()])->first();
		if($users){
			Auth::login($users);
			Session::flash('upgradepremiumstatus', 'Your successfully login'); 
			Session::flash('alert-class', 'alert alert-success');
			return redirect('/dashboard');
		}else{
			$user = User::create([
				'name'          => $userSocial->getName(),
				'email'         => $userSocial->getEmail(),
				'image'         => $userSocial->getAvatar(),
				'provider_id'   => $userSocial->getId(),
				'provider'      => $provider,
			]);
			//return redirect()->route('home');			
			return redirect('/dashboard');					
		}
	}
		
}
