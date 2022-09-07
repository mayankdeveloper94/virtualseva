<?php

namespace App\Http\Middleware;

use App\Department;
use Closure;
use Illuminate\Support\Facades\Auth;

class DepartmentAdmin
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
        //get the current admin
        $department = session('department');



        if(empty($department) || !Department::find($department)){
            return redirect()->route('site.index');
        }

        $user = Auth::user();

        if($user->role_id==1){
            return $next($request);
        }

        //get user department record
        $admin = $user->departments()->where('department_id',$department)->first()->pivot->department_admin;

        if($admin != 1){
            return redirect()->route('member.dashboard');
        }


        return $next($request);
    }
}
