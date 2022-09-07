<?php

namespace App\Http\Middleware;

use App\Models\Currency;
use App\Lib\Helpers;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class SetCurrency
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
        if(!Session::exists('currency_id')){
            $user = Auth::user();

            if(Auth::check() && $user->subscriber()->exists()){
                //user is logged in

                $currencyId = $user->subscriber->currency_id;
                Session::put('currency_id',$currencyId);
            }
            else{

                //get currency from currency table
                $currency = defaultCurrency();

                //$currencyId=2;
                Session::put('currency_id',$currency->id);
            }

        }

        $user = Auth::user();

        if(Auth::check() && $user->subscriber()->exists()){
            //user is logged in

            $currencyId = $user->subscriber->currency_id;

            Session::put('currency_id',$currencyId);

        }


        return $next($request);
    }
}
