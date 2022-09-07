<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\HelpPost;
use Illuminate\Http\Request;

class HelpPostsController extends Controller
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
            $helpposts = HelpPost::whereRaw("match(title,content) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
        } else {
            $helpposts = HelpPost::latest()->paginate($perPage);
        }

        return view('admin.help-posts.index', compact('helpposts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.help-posts.create');
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
            'sort_order'=>'integer'
        ]);
        $requestData = $request->all();
        $requestData['content'] = saveSaasInlineImages($requestData['content']);
        $post = HelpPost::create($requestData);
        $post->helpCategories()->attach($request->categories);
        return redirect('admin/help-posts')->with('flash_message', __('saas.changes-saved'));
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
        $helppost = HelpPost::findOrFail($id);

        return view('admin.help-posts.show', compact('helppost'));
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
        $helppost = HelpPost::findOrFail($id);

        return view('admin.help-posts.edit', compact('helppost'));
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
            'sort_order'=>'integer'
        ]);
        $requestData = $request->all();
        $requestData['content'] = saveSaasInlineImages($requestData['content']);
        
        $helppost = HelpPost::findOrFail($id);
        $helppost->update($requestData);

        $helppost->helpCategories()->sync($request->categories);

        return redirect('admin/help-posts')->with('flash_message', __('saas.changes-saved'));
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
        HelpPost::destroy($id);

        return redirect('admin/help-posts')->with('flash_message', __('saas.record-deleted'));
    }
}
