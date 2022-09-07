<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Field;
use Illuminate\Http\Request;

class FieldsController extends Controller
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
            $fields = Field::latest()->where('name','LIKE',"%{$keyword}%")->paginate($perPage);
        } else {
            $fields = Field::latest()->paginate($perPage);
        }

        return view('admin.fields.index', compact('fields'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.fields.create');
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
            'sort_order'=>"integer",
            'type'=>'required'
        ]);
        $requestData = $request->all();
        
        Field::create($requestData);

        return redirect('admin/fields')->with('flash_message', __('admin.field').' '.__('admin.added'));
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
        $field = Field::findOrFail($id);

        return view('admin.fields.show', compact('field'));
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
        $field = Field::findOrFail($id);

        return view('admin.fields.edit', compact('field'));
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
            'sort_order'=>"integer",
            'type'=>'required'
        ]);
        
        $requestData = $request->all();
        
        $field = Field::findOrFail($id);
        $field->update($requestData);

        return redirect('admin/fields')->with('flash_message', __('admin.field').' '.__('admin.updated'));
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
        Field::destroy($id);
        return redirect('admin/fields')->with('flash_message', __('admin.field').' '.__('admin.deleted'));
    }
}
