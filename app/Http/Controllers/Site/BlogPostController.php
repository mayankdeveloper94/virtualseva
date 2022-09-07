<?php

namespace App\Http\Controllers\Site;

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Lib\HelperTraitSaas;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class BlogPostController extends Controller
{
    use HelperTraitSaas;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function listing(Request $request)
    {
        //
        $title = __('saas.blog');
        $posts = BlogPost::whereDate('published_on','<=',Carbon::now()->toDateTimeString())->where('status',1)->orderBy('published_on','desc');
        $recent = BlogPost::whereDate('published_on','<=',Carbon::now()->toDateTimeString())->where('status',1)->orderBy('published_on','desc')->limit(5)->get();

        $categories = BlogCategory::orderBy('sort_order')->get();

        $category = $request->get('category');
        if(!empty($category) && BlogCategory::find($category)){
            $title = BlogCategory::find($category)->category;
            $posts = $posts->whereHas('blogCategories',function($q) use($category){
                $q->where('id',$category);
            });

        }

        if(!empty($request->q)){
            $keyword = $request->q;
            $posts = $posts->whereRaw("match(title,content,meta_title,meta_description) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
            $title = __('saas.search-results').': '.$request->q;

        }

        $posts = $posts->paginate(20);

        return view('site.blog.listing',compact('posts','recent','title','categories'));
    }

    public function search(Request $request){
        $this->validate($request,[
            'q'=>'required'
        ]);
        $posts = DB::table('blog_posts');
        $posts->whereRaw("match(title,content) against (? IN NATURAL LANGUAGE MODE)", [$request->q]);
        $posts->where('status',1);
        $posts->where('published_on','<',time());
        $results = $posts->paginate(30);

        $recent = BlogPost::whereDate('published_on','<',Carbon::now()->toDateTimeString())->where('status',1)->orderBy('published_on','desc')->limit(5)->get();
        $title = __('saas.search-results').': '.$request->q;
        return view('site.blog.listing',['posts'=>$results,'q'=>$request->q,'recent'=>$recent,'title'=>$title]);
    }

    public function post(BlogPost $blogPost)
    {
        $recent = BlogPost::whereDate('published_on','<=',Carbon::now()->toDateTimeString())->where('status',1)->orderBy('published_on','desc')->limit(5)->get();
        $categories = BlogCategory::orderBy('sort_order')->get();
        $post = $blogPost;
        return view('site.blog.post',compact('post','recent','categories'));
    }

}
