<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Setup
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
        $user = Auth::user();

        //check if user already has subscriber record
        if($user->subscriber()->exists()){
            return redirect()->route('user.dashboard');
        }

        //check if user is not trial
        if(setting('trial_enabled')==0 && $user->trial==1 ){
            return redirect()->route('user.plans');
        }


        return $next($request);
    }
}
