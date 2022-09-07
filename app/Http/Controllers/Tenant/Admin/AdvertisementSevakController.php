<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;
use App\Advertisement;
use App\Department;
use App\User;
use App\Analytic;
use App\AnalyticReport;
use App\Visitor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Carbon\Carbon;

class AdvertisementSevakController extends Controller{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request){
		$advertisements = auth()->user()->advertisements()->paginate(10);	
        return view('site.advertisements.index', compact('advertisements'));
    }
	
	public function share(Advertisement $advertisement, $user_id){
        //dd($advertisement);
        $ip_address = $_SERVER['REMOTE_ADDR'];
        $user_id = Crypt::decrypt($user_id);
        $user = User::findOrFail($user_id);
        $visitor = Visitor::where('user_id', $user_id)->where('advertisement_id', $advertisement->id)->where('ip_address', $ip_address)->get();
        $visitCount = $visitor->count();
        if($visitCount==0){
            $visitins = Visitor::create([
                'user_id' => $user_id,
                'advertisement_id' => $advertisement->id,
                'ip_address' => $ip_address
            ]);
        }
        
        $analytics = Analytic::where('user_id', $user_id)->where('advertisement_id', $advertisement->id)->first();
        if($analytics){
            if($visitCount==0){
                $analytics->no_of_clicks = $analytics->no_of_clicks+1;
                $analytics->save();
                
                $todaysAnalytics = $analytics->reports()->where('date', date('Y-m-d'))->first();
                
                if($todaysAnalytics){
                    $todaysAnalytics->clicks = $todaysAnalytics->clicks+1;
                    $todaysAnalytics->save();
                }else{
                    $todaysAnalytics = $analytics->reports()->create([
                        'date' => date('Y-m-d'),
                        'clicks' => 1,
                    ]);
                }
            }
        }else{
            $analytics = Analytic::create([
                'user_id' => $user_id,
                'advertisement_id' => $advertisement->id,
                'no_of_clicks' => 1,
            ]);
            
            $todaysAnalytics = $analytics->reports()->create([
                'date' => date('Y-m-d'),
                'clicks' => 1,
            ]);
        }
        
        return redirect()->to($advertisement->website_url);
    }
	
	public function analytics(){
        $user_id = auth()->user()->id;
        $advertisements_count = auth()->user()->advertisements()->count();
        $total_clicks = Analytic::where('user_id', $user_id)->sum('no_of_clicks');
        // $todays_clicks = Analytic::where('user_id', $user_id)->whereDate('created_at', Carbon::today())->sum('no_of_clicks');
        $anaylticsIds = Analytic::where('user_id', $user_id)->pluck('id')->toArray();
        $todays_clicks = AnalyticReport::whereIn('analytic_id',$anaylticsIds)->where('date',date('Y-m-d'))->sum('clicks');
        $yesterday_clicks = AnalyticReport::whereIn('analytic_id',$anaylticsIds)->where('date',Carbon::yesterday()->format('Y-m-d'))->sum('clicks');
        return view('site.advertisements.analytics',compact('advertisements_count','total_clicks','todays_clicks','yesterday_clicks'));
    }
	
}
