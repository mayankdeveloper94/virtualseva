<?php

namespace App\Http\Controllers\Subscriber;

use App\Models\Package;
use App\Models\PackageDuration;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PlansController extends Controller
{


    public function index(){

        $packages= Package::where('public',1)->orderBy('sort_order')->get();

        $monthlyPlans = PackageDuration::whereHas('package',function($q){
          $q->where('public',1)->orderBy('sort_order','asc');
        })->where('type','m')->get();

        $annualPlans = PackageDuration::whereHas('package',function($q){
            $q->where('public',1)->orderBy('sort_order','asc');
        })->where('type','a')->get();


        return view('subscriber.plans.index',compact('packages','monthlyPlans','annualPlans'));
    }





}
