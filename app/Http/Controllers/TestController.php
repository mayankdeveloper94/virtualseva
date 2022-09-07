<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Package;
use App\User;
use Hyn\Tenancy\Environment;
use Illuminate\Http\Request;

use Hyn\Tenancy\Models\Website;
use Hyn\Tenancy\Contracts\Repositories\WebsiteRepository;
use Hyn\Tenancy\Models\Hostname;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;

class TestController extends Controller
{
    //
    public function index(){


        $invoice = Invoice::first();
        $invoice = $invoice->replicate();
        $invoice->save();

    exit('testing');
     //  $config = config(['auth.providers.users.model',]);
     //   dd($config);

     /*   $website = new Website;
        app(WebsiteRepository::class)->create($website);
       // dd($website->uuid); // Unique id
        $hostname = new Hostname;
        $hostname->fqdn = 'gfclient1.test';
        $hostname = app(HostnameRepository::class)->create($hostname);
        app(HostnameRepository::class)->attach($hostname, $website);
        dd($website->hostnames); // Collection with $hostname*/

        $website = Website::get()->first();

        $fqdn = 'gfclient1.test';

        //now update tables on client's database
        $tenancy = app(Environment::class);

    //    $tenancy->hostname($fqdn);

   //     $tenancy->hostname(); // resolves $hostname as currently active hostname

        $tenancy->tenant($website); // switches the tenant and reconfigures the app

     //   $tenancy->website(); // resolves $website
    //    $tenancy->tenant(); // resolves $website

     //   $tenancy->identifyHostname(); // resets resolving $hostname by using the Request


        $users = User::get();
       // dd($users);

        $plans = Package::get();
        dd($plans);

        exit('done');
    }

    public function data(){
        $users = User::get();
        $users = $users->toArray();
        dd($users);
    }
}
