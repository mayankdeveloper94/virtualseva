<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\HelpCategory;
use Illuminate\Http\Request;

class HelpCategoriesController extends Controller
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
            $helpcategories = HelpCategory::latest()->paginate($perPage);
        } else {
            $helpcategories = HelpCategory::latest()->paginate($perPage);
        }

        return view('admin.help-categories.index', compact('helpcategories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.help-categories.create');
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
        
        HelpCategory::create($requestData);

        return redirect('admin/help-categories')->with('flash_message', __('saas.changes-saved'));
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
        $helpcategory = HelpCategory::findOrFail($id);

        return view('admin.help-categories.show', compact('helpcategory'));
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
        $helpcategory = HelpCategory::findOrFail($id);

        return view('admin.help-categories.edit', compact('helpcategory'));
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
        
        $helpcategory = HelpCategory::findOrFail($id);
        $helpcategory->update($requestData);

        return redirect('admin/help-categories')->with('flash_message', __('saas.changes-saved'));
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
        HelpCategory::destroy($id);

        return redirect('admin/help-categories')->with('flash_message', __('saas.record-deleted'));
    }
}
