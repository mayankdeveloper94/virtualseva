<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Intervention\Image\Facades\Image;

class BlogPostsController extends Controller
{
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
            $blogposts = BlogPost::whereRaw("match(title,content,meta_title,meta_description) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
        } else {
            $blogposts = BlogPost::latest()->paginate($perPage);
        }

        return view('admin.blog-posts.index', compact('blogposts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.blog-posts.create');
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
        $this->validate($request,[
            'title'=>'required',
            'content'=>'required',
        ]);
        $requestData = $request->all();
        $requestData['user_id'] = Auth::user()->id;

        if($request->hasFile('cover_image')) {
            $path =  $request->file('cover_image')->store('blog','saas_uploads');

            $file = 'saas_uploads/'.$path;
            $img = Image::make($file);

            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($file);

            $requestData['cover_image'] = $file;
        }
        else{
            $requestData['cover_image'] =null;
        }

        $requestData['content'] = saveSaasInlineImages($requestData['content']);

        $blogPost= BlogPost::create($requestData);

        $blogPost->blogCategories()->sync($request->categories);

        return redirect('admin/blog-posts')->with('flash_message', __('saas.changes-saved'));
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
        $blogpost = BlogPost::findOrFail($id);

        return view('admin.blog-posts.show', compact('blogpost'));
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
        $blogpost = BlogPost::findOrFail($id);

        return view('admin.blog-posts.edit', compact('blogpost'));
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
            'title'=>'required',
            'content'=>'required',
        ]);
        $requestData = $request->all();
        
        $blogpost = BlogPost::findOrFail($id);


        if($request->hasFile('cover_image')){
            @unlink($blogpost->cover_image);

            $path =  $request->file('cover_image')->store('blog','saas_uploads');

            $file = 'saas_uploads/'.$path;
            $img = Image::make($file);

            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($file);

            $requestData['cover_image'] = $file;
        }

        $requestData['content'] = saveSaasInlineImages($requestData['content']);

        $blogpost->update($requestData);

        $blogpost->blogCategories()->sync($request->categories);

        return redirect('admin/blog-posts')->with('flash_message', __('saas.changes-saved'));
    }

    public function removePicture($id){
        $dept = BlogPost::find($id);
        @unlink($dept->cover_image);
        $dept->cover_image = null;
        $dept->save();
        return back()->with('flash_message',__('admin.picture').' '.__('admin.deleted'));
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
        BlogPost::destroy($id);

        return redirect('admin/blog-posts')->with('flash_message', __('saas.record-deleted'));
    }
}
