<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Kyc
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
		
        if(setting('general_enable_registration') == 1){
			$user = Auth::user();
			if($user->is_kyc_verified != 1 && $user->role_id != 1){
				return redirect()->route('account.kyc');
			}
        }
        return $next($request);
    }
}
