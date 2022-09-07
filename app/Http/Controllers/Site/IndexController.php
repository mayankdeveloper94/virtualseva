<?php

namespace App\Http\Controllers\Site;

use App\Lib\HelperTraitSaas;
use App\Models\Article;
use App\Models\Currency;
use App\Models\Feature;
use App\Models\Package;
use App\Models\PackageDuration;
use DrewM\MailChimp\MailChimp;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Session;

use App\Models\Slider;
use App\Models\AboutUs;
use App\Models\Service;
use App\Models\OurWork;
use App\Models\FAQ;

class IndexController extends Controller
{
    use HelperTraitSaas;

    //homepage
    public function index(){
        
        $packages= Package::where('public',1)->orderBy('sort_order')->get();

        $monthlyPlans = PackageDuration::whereHas('package',function($q){
            $q->where('public',1)->orderBy('sort_order','asc');
        })->where('type','m')->get();

        $annualPlans = PackageDuration::whereHas('package',function($q){
            $q->where('public',1)->orderBy('sort_order','asc');
        })->where('type','a')->get();
        
        $sliders = Slider::latest()->get();
        
        $aboutUs = AboutUs::first();
        
        $services = Service::get();
        
        $ourWorks = OurWork::get();
        
        $faqs = FAQ::get();

        return view('home',compact('packages','monthlyPlans','annualPlans', 'sliders', 'aboutUs', 'services', 'ourWorks', 'faqs'));
    }


    public function saveEmail(Request $request){
        $this->validate($request,[
            'email'=>'required|email'
        ]);

        $mailChimpKey = setting('mailchimp_api_key');
        $listId = setting('mailchimp_list_id');
        try{
        $MailChimp = new MailChimp($mailChimpKey);


            $result = $MailChimp->post("lists/$listId/members", [
                'email_address' => $request->email,
                'status'        => 'subscribed',
            ]);

            return redirect()->route('site.list')->with('flash_message',__('saas.email-saved'));
        }
        catch(\Exception $ex){
            return redirect()->route('site.list')->with('flash_message',$ex->getMessage());
        }




    }

    public function mailingList(){
        return view('site.index.list');
    }

    public function feature(Feature $feature,$slug){

        return view('site.index.feature',compact('feature'));
    }

    public function article(Article $article,$slug){

        return view('site.index.article',compact('article'));
    }

    public function pricing(){
        $packages= Package::where('public',1)->orderBy('sort_order')->get();

        $monthlyPlans = PackageDuration::whereHas('package',function($q){
            $q->where('public',1)->orderBy('sort_order','asc');
        })->where('type','m')->get();

        $annualPlans = PackageDuration::whereHas('package',function($q){
            $q->where('public',1)->orderBy('sort_order','asc');
        })->where('type','a')->get();


        return view('site.index.pricing',compact('packages','monthlyPlans','annualPlans'));
    }

    public function contact(){

        return view('site.index.contact');
    }

    public function currency($currency){


        Session::put('currency_id',$currency);

        return back();
    }

    public function send(Request $request){
        $this->validate($request,[
           'name'=>'required',
            'email'=>'required|email',
            'message'=>'required'
        ]);

        try{
            $this->sendEmail(setting('general_admin_email'),$request->subject,$request->message,['address'=>$request->email,'name'=>$request->name]);

        }
        catch(\Exception $ex){
            return back()->with('flash_message',$ex->getMessage());
        }


        return back()->with('flash_message',__('saas.message-sent'));

    }

}
