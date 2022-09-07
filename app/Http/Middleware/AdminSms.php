<?php

namespace App\Http\Middleware;

use Closure;

class AdminSms
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
        $dept = getDepartment();
        $enabled = $dept->enable_sms;

        if(empty($enabled)){
            return redirect()->route('member.dashboard')->with('flash_message',__('admin.sms-disabled'));
        }
        return $next($request);
    }
}
