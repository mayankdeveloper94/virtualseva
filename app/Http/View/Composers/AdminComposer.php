<?php
namespace App\Http\View\Composers;

use App\Category;
use App\Department;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AdminComposer 
{


    public function compose(View $view)
    {
        $user = Auth::user();
        //generate name
        $name= limitLength($user->name,10);
        $view->with('name', $name);
        $view->with('picture',userPic($user->id));

        //get list of user's departments
        $departments = $user->departments()->orderBy('name')->limit(10)->get();
        $view->with('userDepartments',$departments);

        //get categories
        $categories = Category::orderBy('name')->limit(10)->get();
        $view->with('categories',$categories);

        //get logo
        $logo = setting('image_logo');
        if(empty($logo)){
            $logo = false;
        }
        $view->with('logo',$logo);

        $view->with('siteName',setting('general_site_name'));

        $icon = setting('image_icon');
        if(empty($icon)){
            $icon = false;
        }
        $view->with('icon',$icon);

        $inboxMail = $user->receivedEmails()->latest()->limit(20)->get();
        $view->with('emails',$inboxMail);
        $inboxSms = $user->receivedSms()->latest()->limit(20)->get();
        $view->with('sms',$inboxSms);

        //do department stuff
        $departmentId = session('department');
        if($departmentId && Department::find($departmentId)){

            $department = Department::find($departmentId);
            $view->with('department',$department);

            $announcements = $department->announcements()->latest()->limit(20)->get();
            $view->with('announcements',$announcements);

        }

    }
}