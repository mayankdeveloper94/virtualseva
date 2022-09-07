<?php

namespace App\Http\Controllers\Subscriber;

use App\Lib\Helpers;
use App\Models\Country;
use App\Models\PackageDuration;
use App\Setting;
use App\User;
use Hyn\Tenancy\Models\Website;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Environment;

class SetupController extends Controller
{


    public function index(Request $request){
        //get countries
        $domain = $_SERVER['SERVER_NAME'];

        return view('subscriber.setup.index',compact('domain'));
    }

    public function process(Request $request){


        $username = safeUrl($request->username);
        $username = str_ireplace('_', '-', $username);
        $fqdn = $username.'.'.$_SERVER['SERVER_NAME'];

        $request->request->add(['fqdn' => $fqdn]); //add request



        $messages = [
            'fqdn.required' => __('saas.username-required'),
            'fqdn.unique' => __('saas.username-exists'),
        ];

        $this->validate($request,[
            'fqdn'=>'required|unique:hostnames|min:3|max:200',
            'username'=>'required|min:3|max:30',
            'name'=>'required',
            'email'=>'required|email',
            'password'=>'required|confirmed',
            'general_site_name'=>'required',
            'general_admin_email'=>'required|email',
            'general_tel'=>'required',
        ],$messages);

        $language= App::getLocale();


        //get trial settings

        $destinationPackage = false;
        $subscriptionSeconds = false;


        $user = Auth::user();
        if($user->trial==0){
            //get invoice
            $invoice = $user->invoices()->where('paid',1)->where('invoice_purpose_id',1)->orderBy('id','desc')->first();
            if(!$invoice){
               // return back()->with('flash_message',__('saas.no-invoice'));
                $trialPackageId = setting('trial_package_duration_id');
                if(empty($trialPackageId) || !PackageDuration::find($trialPackageId)){
                    //get random package
                    $packageDuration = PackageDuration::orderBy('type','desc')->first();
                    if(!$packageDuration){
                        return back()->with('flash_message',__('saas.no-plans'));
                    }
                    $destinationPackage = $packageDuration->id;
                }
                else{
                    $destinationPackage = $trialPackageId;

                }

            }
            else{
                $packageDurationId = $invoice->item_id;
                if(!PackageDuration::find($packageDurationId)){
                    return back()->with('flash_message',__('saas.invalid-plan'));
                }
                $packageDuration =PackageDuration::find($packageDurationId);
                $destinationPackage= $packageDuration->id;
                $subscriptionSeconds = $packageDuration->seconds;
            }


        }
        else{
            $trialPackageId = setting('trial_package_duration_id');
            if(empty($trialPackageId) || !PackageDuration::find($trialPackageId)){
                //get random package
                $packageDuration = PackageDuration::orderBy('type','desc')->first();
                if(!$packageDuration){
                    return back()->with('flash_message',__('saas.no-plans'));
                }
                $destinationPackage = $packageDuration->id;
            }
            else{
                $destinationPackage = $trialPackageId;

            }

            $subscriptionSeconds = 86400 * intval(setting('trial_days'));
        }





        //$hostname= $request->username.'.'.$_SERVER['SERVER_NAME'];
        $website = new Website();
        app(WebsiteRepository::class)->create($website);
        // dd($website->uuid); // Unique id
        $hostname = new Hostname;
        $hostname->fqdn = $fqdn;
        $hostname = app(HostnameRepository::class)->create($hostname);
        app(HostnameRepository::class)->attach($hostname, $website);
        // dd($website->hostnames); // Collection with $hostname

        //new create subscriber
        $user->subscriber()->create([
           'website_id'=>$website->id,
            'package_duration_id'=>$destinationPackage,
            'expires'=>time() + $subscriptionSeconds,
            'currency_id'=> defaultCurrency()->id
        ]);

        $url = route('user.dashboard');
        //now update tables on client's database
        $tenancy = app(Environment::class);
        $tenancy->tenant($website); // switches the tenant and reconfigures the app
        config(['database.default' => 'tenant']);

        //first update user
        $user= User::first();
        if($user){
            $user->name = $request->name;
            $user->password = Hash::make($request->password);
            $user->email = $request->email;
            $user->save();
        }

        //update settings
        Setting::where('key','general_site_name')->update(['value'=>$request->general_site_name]);
        Setting::where('key','general_admin_email')->update(['value'=>$request->general_admin_email]);
        Setting::where('key','general_tel')->update(['value'=>$request->general_tel]);
        Setting::where('key','config_language')->update(['value'=>$language]);


        return redirect($url)->with('flash_message',__('saas.setup-complete'));

    }

    public function username(Request $request){
        $data = $request->all();

        $username = safeUrl($data['username']);
        $username = str_ireplace('_', '-', $username);
        $fqdn = $username.'.'.$_SERVER['SERVER_NAME'];
        $data['fqdn'] = $fqdn;
        $validator = Validator::make($data,[
            'fqdn'=>'unique:hostnames'
        ]);

        if($validator->fails()){
            $status= false;
        }
        else{
            $status=true;
        }
        return response()->json(['status'=> $status]);
    }

}
