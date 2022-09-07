<?php

namespace App\Http\Controllers\Tenant;

use App\Category;
use App\Department;
use App\Lib\CronJobs;
use App\User;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

use App\Setting;
use App\Sociallink;
use App\Carouselslider;
use App\Herowidget;
use App\FormField;
use App\FormOption;
use App\Leadform;
use App\Leadformsdata;
use App\AboutUs;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
   //     $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function site(Request $request){
		
		$sociallinks 		= Sociallink::orderBy('id')->paginate(1000);
		
		$group		 		= 'frontsettings';
		
		$frontsettings 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();
		$carouselsliders 	= Carouselslider::orderBy('id')->paginate(1000);	
		
		$group		 		= 'banner';
		
		$banners 	 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();		
		$herowidgets 		= Herowidget::orderBy('id')->paginate(1000);	
		
		$forms 				= FormField::where('field_enabled',1)->orderBy('field_sortorder')->paginate(1000);
		
		
        //get department list
        $keyword = $request->get('search');
        $category = $request->get('category');
        $perPage = 9;

        $departments = Department::inRandomOrder()->limit($perPage)->where('visible',1);

        if(!empty($category) && Category::find($category)){
            $categoryName = Category::find($category)->name;

            $departments = $departments->whereHas('categories',function($q) use ($category){
                $q->where('id',$category);
            });

        }
        else{
            $categoryName = null;
        }

        $departments = $departments->get();
        
        $aboutUs = AboutUs::first();

        return view('welcome', compact('departments','categoryName','sociallinks','frontsettings','carouselsliders','banners','herowidgets','forms','aboutUs'));
    }

    public function cron(){
        $cronJobs = new CronJobs();
        $cronJobs->deleteTempFiles();
        $cronJobs->upcomingEvents();
        echo 'Cron Complete';
    }

    public function privacy(){
        return view('privacy');
    }

    public function test(){
        $user = User::find(2);
        $this->saveUserPicture($user,'https://www.nairaland.com/vertipics/wv17v3a3nhus3lm7e4l3f363wafwv3v1.jpg');
        echo 'done';
    }

    private function saveUserPicture(User $user,$photoUrl){
        if(!empty($user->picture)){
            return true;
        }

        if(empty($photoUrl)){
            return true;
        }

        //download the image
        try{
            $remoteName = basename($photoUrl);
            $filename = time().'-'.$remoteName;
            $tempImage = tempnam(sys_get_temp_dir(), $filename);
            copy($photoUrl, $tempImage);

            $path_parts = pathinfo($photoUrl);

            $extension = $path_parts['extension'];


            $file = 'uploads/members/'.uniqid().'.'.$extension;
            $img = Image::make($tempImage);

            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($file);

            $user->picture = $file;
            $user->save();
            @unlink($tempImage);

        }
        catch(\Exception $ex){

        }



    }

}

