<?php

namespace App\Http\Middleware;

use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;

class UserLimit
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

        if(saas() && defined('USER_LIMIT')){
            $totalUsers = User::count();
            if($totalUsers > USER_LIMIT){
                if (Auth::check()){
                    $user = Auth::user();
                    if ($user->role_id==1){
                        return redirect()->route('admin.members.index')->with('flash_message',__('site.limit-exceeded'));

                    }

                }
                return redirect()->route('home')->with('flash_message',__('site.limit-exceeded'));

            }


        }
        return $next($request);
    }
}
