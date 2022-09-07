<?php

namespace App\Http\Controllers\Tenant\Site;

use App\Application;
use App\Category;
use App\Department;
use App\Lib\HelperTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Tenant\Controller;
use Illuminate\Support\Facades\Auth;

use App\Setting;
use App\Sociallink;
use App\Carouselslider;
use App\Herowidget;
use App\FormField;
use App\FormOption;
use App\Leadform;
use App\Leadformsdata;

class DepartmentsController extends Controller
{
    use HelperTrait;

    public function index(Request $request){
        
        $sociallinks 		= Sociallink::orderBy('id')->paginate(1000);
		
		$group		 		= 'frontsettings';
		
		$frontsettings 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();
		$carouselsliders 	= Carouselslider::orderBy('id')->paginate(1000);	
		
		$group		 		= 'banner';
		
		$banners 	 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();		
		$herowidgets 		= Herowidget::orderBy('id')->paginate(1000);	
		
		$forms 				= FormField::where('field_enabled',1)->orderBy('field_sortorder')->paginate(1000);
		
		
		if(setting('general_enable_kyc')==1){
			if(Auth::check()){
				$user = Auth::user();
				if($user->is_kyc_verified != 1 && $user->role_id != 1){
					return redirect()->route('account.kyc');
				}
			}
		}

        //get department list
        $keyword = $request->get('search');
        $category = $request->get('category');
        $perPage = 21;

	    $categoryName = '';
        if (!empty($keyword)) {
            $departments = Department::where('visible',1)->whereRaw("match(name,description) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
        } else {
            $departments = Department::orderBy('name')->where('visible',1);
        }

        if(!empty($category) && Category::find($category)){
            
            $categoryName = Category::find($category)->name;

            $departments = $departments->whereHas('categories',function($q) use ($category){
                $q->where('id',$category);
            });

        }


        $departments = $departments->paginate($perPage);



        

        return view('site.departments.index', compact('departments','categoryName', 'sociallinks','frontsettings','carouselsliders','banners','herowidgets','forms'));
    }

    public function details(Department $department){

		if(setting('general_enable_kyc')==1){
			if(Auth::check()){
				$user = Auth::user();
				if($user->is_kyc_verified != 1 && $user->role_id != 1){
					return redirect()->route('account.kyc');
				}
			}
		}

        $gallery = $department->galleries()->paginate(50);

        return view('site.departments.details',compact('department','gallery'));
    }

    public function login(Department $department){
        $this->loginToDepartment($department->id);

        //redirect to department dashboard
        return redirect()->route('member.dashboard');
    }

    public function myDepartments(){
        //get list of all departments for user
        $user = Auth::user();

        $departments = $user->departments()->orderBy('name')->paginate(20);
        return view('site.departments.my-departments',compact('departments'));
    }

    public function join(Department $department){
        
        //check that department allows enrollment
        if(!($department->enroll_open==1 && $department->approval_required==0 && $department->enabled==1 && $department->visible==1)){
            return back()->with('flash_message',__('site.join-error'));
        }

        $user = Auth::user();

        //check if user has department
       $total= $user->departments()->where('id',$department->id)->count();

       if(empty($total)){
           $user->departments()->attach($department->id);
       }

        $this->loginToDepartment($department->id);

        //redirect to department dashboard
        return redirect()->route('member.dashboard')->with('flash_message',__('site.join-success',['name'=>$department->name]));
    }

    public function apply(Department $department){
        
        $sociallinks 		= Sociallink::orderBy('id')->paginate(1000);
		
		$group		 		= 'frontsettings';
		
		$frontsettings 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();
		$carouselsliders 	= Carouselslider::orderBy('id')->paginate(1000);	
		
		$group		 		= 'banner';
		
		$banners 	 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();		
		$herowidgets 		= Herowidget::orderBy('id')->paginate(1000);	
		
		$forms 				= FormField::where('field_enabled',1)->orderBy('field_sortorder')->paginate(1000);
		
        if(!$this->canApply($department)){
            return back();
        }
        $user = Auth::user();
        //get total fields
        $fields = $department->departmentFields()->where('enabled',1)->count();

        if(empty($fields)){
            $application = Application::create([
                'user_id'=>$user->id,
                'department_id'=>$department->id,
                'status'=>'p',
            ]);

            return redirect()->route('site.my-applications')->with('flash_message',__('site.application-message',['name'=>$department->name]));
        }

        $fields = $department->departmentFields()->where('enabled',1)->get();

        return view('site.departments.apply',compact('fields','department','user', 'sociallinks','frontsettings','carouselsliders','banners','herowidgets','forms'));
    }

    public function saveApplication(Request $request,Department $department){

        $requestData = $request->all();

        if(!$this->canApply($department)){
            return back();
        }

        $fields = $department->departmentFields()->where('enabled',1)->get();
        $rules = [];

        foreach($fields as $field){
            if($field->required==1){
                $rules["field_{$field->id}"]='required';
            }
        }



        if(!empty($rules)){
            $this->validate($request,$rules);
        }

        $user = Auth::user();


        $customValues = [];
        //attach custom values
        foreach($fields as $field){
            if(isset($requestData['field_'.$field->id]))
            {
                $customValues[$field->id] = ['value'=>$requestData['field_'.$field->id]];
            }


        }

        $user->departmentFields()->sync($customValues);

        Application::create([
            'user_id'=>$user->id,
            'department_id'=>$department->id,
            'status'=>'p',
        ]);

        return redirect()->route('site.my-applications')->with('flash_message',__('site.application-message',['name'=>$department->name]));



    }


    public function myApplications(){
        
        $sociallinks 		= Sociallink::orderBy('id')->paginate(1000);
		
		$group		 		= 'frontsettings';
		
		$frontsettings 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();
		$carouselsliders 	= Carouselslider::orderBy('id')->paginate(1000);	
		
		$group		 		= 'banner';
		
		$banners 	 		= Setting::where('key','LIKE',"{$group}_%")->orderBy('sort_order')->get();		
		$herowidgets 		= Herowidget::orderBy('id')->paginate(1000);	
		
		$forms 				= FormField::where('field_enabled',1)->orderBy('field_sortorder')->paginate(1000);
		

        $user = Auth::user();
        $applications = $user->applications()->latest()->paginate(30);
        return view('site.departments.my_applications',compact('applications','user', 'sociallinks','frontsettings','carouselsliders','banners','herowidgets','forms'));
    }


    public function deleteApplication(Application $application){
        $this->authorize('delete',$application);
        $application->delete();
        return back()->with('flash_message',__('site.deleted'));
    }

    private function canApply(Department $department){

        if(!($department->enroll_open==1 && $department->approval_required==1 && $department->enabled==1 && $department->visible==1)){
            $this->errorMessage(request(),__('site.join-error'));
            return false;

        }

        //check if pending application exists already for this department
        $user = Auth::user();
        $count = $user->applications()->where('department_id',$department->id)->where('status','p')->count();
        if(!empty($count)){
            $this->errorMessage(request(),__('site.application-error'));
            return false;

        }

        return true;
    }

}
