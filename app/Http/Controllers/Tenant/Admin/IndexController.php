<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Department;
use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Tenant\Controller;
use Illuminate\Support\Facades\Auth;

class IndexController extends Controller
{

    public function dashboard(){

        $output=[];
        $output['departments'] = Department::count();
        $output['members'] = User::where('role_id',2)->count();
        $output['admins'] = User::where('role_id',1)->count();
        $output['messages'] = Auth::user()->receivedEmails()->count();
        $output['user'] = Auth::user();
        $output['newMembers']= User::where('role_id',2)->latest()->limit(4)->get();
        $output['emails'] =Auth::user()->receivedEmails()->latest()->limit(10)->get();
        
        $months = array_map('getMonthStr', range(-7,0));
        
        $monthlyMemberCount = [];
        
        foreach(range(-7,0) as $offset){
            //get the
            $start= date("Y-m-d", strtotime("$offset months first day of this month"));
            $end = date("Y-m-d", strtotime("$offset months last day of this month"));
            $monthlyMemberCount[] = User::where('role_id',2)->whereDate('created_at','>=', $start)->whereDate('created_at','<=', $end)->count();
        }

        $output['monthlyMemberCount'] = json_encode($monthlyMemberCount);
        
        $output['monthlyMemberMin'] = min($monthlyMemberCount);
        $output['monthlyMemberMax'] = max($monthlyMemberCount);
        
        $output['monthList']= json_encode($months);
    
        return view('admin.index.dashboard',$output);
    }
    
    
}
