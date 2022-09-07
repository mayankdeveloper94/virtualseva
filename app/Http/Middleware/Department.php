<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Department
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
        $departmentId = session('department');

        if(empty($departmentId)){
            return redirect()->route('site.select-department');
        }

        //check if user is admin
        if($user->role_id==1){
            return $next($request);
        }

        $department = $user->departments()->where('department_id',$departmentId)->first();

        //user is not a member of this department
        if(!$department){
            return redirect()->route('site.select-department');
        }

        return $next($request);
    }
}
