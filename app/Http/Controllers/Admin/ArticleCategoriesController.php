<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\ArticleCategory;
use Illuminate\Http\Request;

class ArticleCategoriesController extends Controller
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
            $articlecategories = ArticleCategory::latest()->paginate($perPage);
        } else {
            $articlecategories = ArticleCategory::latest()->paginate($perPage);
        }

        return view('admin.article-categories.index', compact('articlecategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.article-categories.create');
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
            'name'=>'required',
            'sort_order'=>'integer'
        ]);
        $requestData = $request->all();
        
        ArticleCategory::create($requestData);

        return redirect('admin/article-categories')->with('flash_message', __('saas.changes-saved'));
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
        $articlecategory = ArticleCategory::findOrFail($id);

        return view('admin.article-categories.show', compact('articlecategory'));
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
        $articlecategory = ArticleCategory::findOrFail($id);

        return view('admin.article-categories.edit', compact('articlecategory'));
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
            'name'=>'required',
            'sort_order'=>'integer'
        ]);
        $requestData = $request->all();
        
        $articlecategory = ArticleCategory::findOrFail($id);
        $articlecategory->update($requestData);

        return redirect('admin/article-categories')->with('flash_message', __('saas.changes-saved'));
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
        ArticleCategory::destroy($id);

        return redirect('admin/article-categories')->with('flash_message', __('saas.record-deleted'));
    }
}
