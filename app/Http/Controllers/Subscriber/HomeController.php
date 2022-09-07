<?php

namespace App\Http\Controllers\Subscriber;

use App\Announcement;
use App\Department;
use App\Download;
use App\Email;
use App\Event;
use App\ForumTopic;
use App\Models\Invoice;
use App\Models\User;
use App\Sms;
use Hyn\Tenancy\Environment;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class HomeController extends Controller
{


    public function index(){

        //check if user has subscription account
        $user = Auth::user();
        $freeTrial = setting('trial_enabled');

        if(($user->trial==0 && !$user->subscriber()->exists()) || ($user->trial==1 && !$user->subscriber()->exists() && $freeTrial==1)){
            return redirect()->route('user.setup');
        }

        if($user->subscriber()->exists() && $user->subscriber->expires < time()){
            //check for invoice
            $invoice = $user->invoices()->where('paid',0)->where('invoice_purpose_id',1)->where('item_id',$user->subscriber->package_duration_id)->latest()->first();
            if(!$invoice){
                $invoice = Invoice::create([
                    'user_id'=>$user->id,
                    'invoice_purpose_id'=>1,
                    'amount'=> $user->subscriber->packageDuration->price,
                    'paid'=>0,
                    'item_id'=>$user->subscriber->package_duration_id,
                    'currency_id'=>$user->subscriber->currency_id
                ]);
            }

            return redirect()->route('user.billing.pay',['invoice'=>$invoice->id])->with('flash_message',__('saas.subscription-expired'));
        }

        $subscribed = $user->subscriber()->exists();

        //get current plan info


        //get domain url


        //get recent invoices


        return view('subscriber.home.dashboard',compact('user','subscribed'));
    }

    public function stats(){

        return view('subscriber.home.stats');
    }

    public function getStats(){

        $user = Auth::user();
        //calculate disk space usage
        //get website id
        $stats=[];

        if($user->subscriber()->exists()){

            $folder = 'uploads/'.$user->subscriber->website_id;
            $size = filesize_r($folder);
            $stats['disk'] = human_filesize($size);
            $stats['limit'] = $user->subscriber->packageDuration->package->storage_space.strtoupper($user->subscriber->packageDuration->package->storage_unit);
            $stats['package'] = $user->subscriber->packageDuration->package;
            //connect to website
            $website = $user->subscriber->website;

            $tenancy = app(Environment::class);
            $tenancy->tenant($website);
            config(['database.default' => 'tenant']);

            $stats['users'] = \App\User::count();
            $stats['departments'] = Department::count();
            $stats['forum_topics'] = ForumTopic::count();
            $stats['events'] = Event::count();
            $stats['downloads'] = Download::count();
            $stats['announcements'] = Announcement::count();
            $stats['emails'] = Email::count();
            $stats['sms'] = Sms::count();

        }
        else{
            exit(__('saas.no-data'));
        }


        return view('admin.subscribers.stats', ['subscriber'=>$user,'stats'=>$stats]);
    }


    public function domains(){
        $user = Auth::user();
        if(!$user->subscriber()->exists()){
            return back()->with('flash_message',__('saas.no-domains'));
        }

        $domains = $user->subscriber->website->hostnames;
        return view('subscriber.home.domains',compact('domains'));
    }

    public function saveDomain(Request $request){
        $data = $request->all();

        $username = safeUrl($data['username']);
        $username = str_ireplace('_', '-', $username);
        $fqdn = $username.'.'.$_SERVER['SERVER_NAME'];
        $data['fqdn'] = $fqdn;
        $validator = Validator::make($data,[
            'fqdn'=>'unique:hostnames',
            'username'=>'required|min:3',
        ]);

        if($validator->fails()){
            return back()->with('flash_message',__('saas.invalid-username'))->with('username',$data['username']);
        }

        $user = Auth::user();
        $domain = $user->subscriber->website->hostnames()->first();

        $domain->fqdn = $fqdn;
        $domain->save();
        return back()->with('flash_message',__('saas.changes-saved'));

    }

    public function profile(){

        $user= Auth::user();
        return view('subscriber.home.profile',compact('user'));
    }

    public function saveProfile(Request $request){

        $requestData = $request->all();
        $user = Auth::user();
        $rules = [
            'name'=>'required',
            'email'=>'required|email'
        ];

        if($requestData['email']!=$user->email){
            $rules['email'] = 'required|email|unique:users';
        }

        $this->validate($request,$rules);

        if(!empty($requestData['password'])){
            $requestData['password']= Hash::make($requestData['password']);
        }
        else{
            $requestData['password'] = $user->password;
        }

        $user->fill($requestData);
        $user->save();

        return back()->with('flash_message',__('admin.changes-saved'));
    }


}
