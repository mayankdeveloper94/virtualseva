<?php
namespace App\Http\View\Composers;
use App\Category;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class SiteComposer 
{

    public function compose(View $view)
    {
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

        if(Auth::check()){
            $user = Auth::user();
            //generate name
            $name= limitLength($user->name,15);
            $view->with('name', $name);
            $view->with('picture',userPic($user->id));

            //get list of user's departments
            $departments = $user->departments()->orderBy('name')->limit(10)->get();
            $view->with('userDepartments',$departments);
        }

    }

}