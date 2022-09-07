<?php

namespace App\Http\Controllers\Admin;

use App\Announcement;
use App\Department;
use App\Download;
use App\Email;
use App\Event;
use App\ForumTopic;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Lib\HelperTrait;
use App\Models\PackageDuration;
use App\Models\User;
use App\Setting;
use App\Sms;
use Hyn\Tenancy\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;
use Hyn\Tenancy\Environment;

class SubscribersController extends Controller
{
    use HelperTrait;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 25;

        if (!empty($keyword)) {
            $subscribers = User::where('role_id',2)->whereRaw("match(name,email) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
        } else {
            $subscribers = User::where('role_id',2)->orderBy('name');
        }

        $sort= $request->get('sort');
        $title = __('saas.all-subscribers');
        switch($sort){
            case 'c':
                $subscribers = $subscribers->where('trial',0)->whereHas('subscriber',function($q){
                    $q->where('expires','>=',time());
                    $q->orderBy('expires','asc');
                });
                $title = __('saas.active-customers');
                break;
            case 't':
                $subscribers = $subscribers->where('trial',1)->whereHas('subscriber',function($q){
                    $q->where('expires','>=',time());
                    $q->orderBy('expires','asc');
                });
                $title = __('saas.active-trials');
                break;
            case 'ec':
                $subscribers = $subscribers->where('trial',0)->whereHas('subscriber',function($q){
                    $q->where('expires','<',time());
                    $q->orderBy('expires','asc');
                });
                $title = __('saas.expired-customers');
                break;
            case 'et':
                $subscribers = $subscribers->where('trial',1)->whereHas('subscriber',function($q){
                    $q->where('expires','<',time());
                    $q->orderBy('expires','asc');
                });
                $title = __('saas.expired-trials');
                break;
        }

        $title .= ': '.$subscribers->count();

        $subscribers = $subscribers->paginate($perPage);


        return view('admin.subscribers.index', compact('subscribers','title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $packages = PackageDuration::get();
        $currencyId = defaultCurrency()->id;
        return view('admin.subscribers.create',compact('packages','currencyId'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request)
    {
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
            'email'=>'required|email|unique:users',
            'password'=>'required|min:6',
            'general_site_name'=>'required',
            'general_tel'=>'required',
            'package_duration_id'=>'required',
            'currency_id'=>'required'
        ],$messages);


        $requestData = $request->all();
        
        // dd($requestData);
        
        $requestData['role_id']=2;
        $requestData['password'] = Hash::make($requestData['password']);
        $user= User::create($requestData);

        // dd($user);

        $language= App::getLocale();
        
        // dd($language);

        //get trial settings

        $subscriptionSeconds = false;
        $packageDuration = PackageDuration::find($request->package_duration_id);
        $destinationPackage= $packageDuration->id;
        
        // dd($packageDuration);
        
        if($user->trial==0){
            //get invoice
            $subscriptionSeconds = $packageDuration->seconds;
        }
        else{

            $subscriptionSeconds = 86400 * intval(setting('trial_days'));
        }

        //$hostname= $request->username.'.'.$_SERVER['SERVER_NAME'];
        $website = new Website();
        // dd($website);
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
            'currency_id'=> $request->currency_id
        ]);

        $url = url('admin/subscribers');
        //now update tables on client's database
        //  $tenancy = app(Environment::class);
        //  $tenancy->tenant($website); // switches the tenant and reconfigures the app
        $tenancy = app(Environment::class);
        $tenancy->tenant($website);
        config(['database.default' => 'tenant']);

        //first update user
        $user= \App\User::first();
        if($user){
            $user->name = $request->name;
            $user->password = Hash::make($request->password);
            $user->email = $request->email;
            $user->save();
        }

        //update settings
        Setting::where('key','general_site_name')->update(['value'=>$request->general_site_name]);
        Setting::where('key','general_admin_email')->update(['value'=>$request->email]);
        Setting::where('key','general_tel')->update(['value'=>$request->general_tel]);
        Setting::where('key','config_language')->update(['value'=>$language]);

        try{
            $subject = __('saas.new-account');
            $message = __('saas.new-account-msg',['site-name'=>setting('general_site_name'),'email'=>$request->email,'password'=>$request->password,'link'=>url('/login')]);
            $this->sendEmail($request->email,$subject,$message);
        }
        catch(\Exception $ex){

        }

        return redirect()->to($url)->with('flash_message', __('saas.changes-saved'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function show($id)
    {
        $subscriber = User::findOrFail($id);




        return view('admin.subscribers.show', compact('subscriber'));
    }

    public function stats(User $user){

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


        return view('admin.subscribers.stats', ['subscriber'=>$user,'stats'=>$stats]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     *
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $subscriber = User::findOrFail($id);
        $packages = PackageDuration::get();

        $plan= null;
        $expires = null;
        $currency=null;
        if($subscriber->subscriber()->exists()){
            $plan = $subscriber->subscriber->package_duration_id;
            $expires= date('Y-m-d',$subscriber->subscriber->expires);
            $currencyId = $subscriber->subscriber->currency_id;
        }

        return view('admin.subscribers.edit', compact('subscriber','packages','plan','expires','currencyId'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function update(Request $request, $id)
    {
        $this->validate($request,[
           'name'=>'required',
            'email'=>'required',
            'expires'=>'required',
            'package_duration_id'=>'required'
        ]);
        $requestData = $request->all();
        $subscriber = User::findOrFail($id);
        if(!empty($requestData['password'])){
            $requestData['password']= Hash::make($requestData['password']);
        }
        else{
            $requestData['password'] = $subscriber->password;
        }


        $subscriber->update($requestData);

        //update subscription
        $requestData['expires'] = strtotime($requestData['expires']);



        if($subscriber->subscriber()->exists()){
            $subscriber->subscriber->update($requestData);
        }

        return redirect('admin/subscribers')->with('flash_message',__('saas.changes-saved'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function destroy($id)
    {



        //delete user
        $user = User::find($id);
        if($user->subscriber()->exists()){
            $dir = 'uploads/'.$user->subscriber->website_id;
            try{
                deleteDir($dir);
            }
            catch(\Exception $ex){

            }
            //remove website
            $website = Website::find($user->subscriber->website_id);
            $prefix = $website->id.'_';

            //drop tables

            if(config('tenancy.db.tenant-division-mode')=='prefix'){
                //get database name
                $dbName = config('database.connections.system.database');
                \Illuminate\Support\Facades\DB::statement("CALL drop_tables_like('{$prefix}%', '{$dbName}')");

            }


            foreach($website->hostnames as $hostname){
                app(HostnameRepository::class)->delete($hostname, true);
            }

            app(WebsiteRepository::class)->delete($website, true);



        }


        User::destroy($id);


        return redirect('admin/subscribers')->with('flash_message', __('saas.record-deleted'));
    }

    public function search(Request $request){
        $keyword = $request->get('term');

        if(empty($keyword)){
            return response()->json([]);
        }

        $members = User::where('role_id',2)->whereRaw("match(name,email) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->limit(500)->get();

        $formattedUsers = [];

        foreach($members as $member){

                $formattedUsers[] = ['id'=>$member->id,'text'=>"{$member->name} ({$member->email})"];


        }

        // $formattedUsers['pagination']=['more'=>false];
        return response()->json($formattedUsers);
    }
}
