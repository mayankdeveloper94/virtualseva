<?php

namespace App\Http\Controllers\Site;

use App\Models\HelpCategory;
use App\Models\HelpPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class DocsController extends Controller
{
    //
    public function index(){

        $categories = HelpCategory::orderBy('sort_order')->get();
        return view('site.docs.index',['categories'=>$categories]);
    }

    public function post($id,$slug){
        $post = HelpPost::find($id);

        //get previous and next posts
        $previous = HelpPost::where('sort_order','<',$post->sort_order)->orderBy('sort_order','desc')->first();
        $next = HelpPost::where('sort_order','>',$post->sort_order)->orderBy('sort_order','asc')->first();

        return view('site.docs.post',compact('post','previous','next'));
    }

    public function search(Request $request){

        $posts = DB::table('help_posts');
        $posts->whereRaw("match(title,content) against (? IN NATURAL LANGUAGE MODE)", [$request->q]);
        $posts->where('status',1);
        $results = $posts->paginate(30);
        return view('site.docs.search',['posts'=>$results,'q'=>$request->q]);
    }

    public function category($id){

            $category = HelpCategory::find($id);
        return view('site.docs.category',compact('category'));
    }

    public function download(Request $request){
        set_time_limit(3600);
        $categories = HelpCategory::orderBy('sort_order')->get();

        $pdf = App::make('dompdf.wrapper');
        $pdf->loadView('site.docs.download', ['categories'=>$categories])->setPaper('a4', 'portrait');
        return $pdf->download('user_guide_'.date('d_m_y').'.pdf');

    }
}
