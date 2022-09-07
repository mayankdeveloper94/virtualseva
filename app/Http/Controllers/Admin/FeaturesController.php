<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Feature;
use Illuminate\Http\Request;

class FeaturesController extends Controller
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
            $features = Feature::latest()->paginate($perPage);
        } else {
            $features = Feature::latest()->paginate($perPage);
        }

        return view('admin.features.index', compact('features'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.features.create');
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
           'menu_title'=>'required',
            'page_title'=>'required',
            'content'=>'required',
            'sort_order'=>'integer'
        ]);
        $requestData = $request->all();
        $requestData['content'] = saveSaasInlineImages($requestData['content']);
        Feature::create($requestData);

        return redirect('admin/features')->with('flash_message', __('saas.changes-saved'));
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
        $feature = Feature::findOrFail($id);

        return view('admin.features.show', compact('feature'));
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
        $feature = Feature::findOrFail($id);

        return view('admin.features.edit', compact('feature'));
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
            'menu_title'=>'required',
            'page_title'=>'required',
            'content'=>'required',
            'sort_order'=>'integer'
        ]);
        $requestData = $request->all();
        $requestData['content'] = saveSaasInlineImages($requestData['content']);
        $feature = Feature::findOrFail($id);
        $feature->update($requestData);

        return redirect('admin/features')->with('flash_message', __('saas.changes-saved'));
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
        Feature::destroy($id);

        return redirect('admin/features')->with('flash_message', __('saas.record-deleted'));
    }
}
