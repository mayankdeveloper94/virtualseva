<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\BlogCategory;
use Illuminate\Http\Request;

class BlogCategoriesController extends Controller
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
            $blogcategories = BlogCategory::latest()->paginate($perPage);
        } else {
            $blogcategories = BlogCategory::latest()->paginate($perPage);
        }

        return view('admin.blog-categories.index', compact('blogcategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.blog-categories.create');
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
            'category'=>'required',
            'sort_order'=>'integer'
        ]);
        $requestData = $request->all();
        
        BlogCategory::create($requestData);

        return redirect('admin/blog-categories')->with('flash_message', __('saas.changes-saved'));
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
        $blogcategory = BlogCategory::findOrFail($id);

        return view('admin.blog-categories.show', compact('blogcategory'));
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
        $blogcategory = BlogCategory::findOrFail($id);

        return view('admin.blog-categories.edit', compact('blogcategory'));
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
            'category'=>'required',
            'sort_order'=>'integer'
        ]);
        $requestData = $request->all();
        
        $blogcategory = BlogCategory::findOrFail($id);
        $blogcategory->update($requestData);

        return redirect('admin/blog-categories')->with('flash_message', __('saas.changes-saved'));
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
        BlogCategory::destroy($id);

        return redirect('admin/blog-categories')->with('flash_message', __('saas.record-deleted'));
    }
}
