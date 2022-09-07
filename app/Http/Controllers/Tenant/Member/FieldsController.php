<?php

namespace App\Http\Controllers\Tenant\Member;

 
use App\Http\Controllers\Tenant\Controller;

use App\DepartmentField as Field;
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
            $fields = getDepartment()->departmentFields()->where('name','LIKE',"%{$keyword}%")->paginate($perPage);
        } else {
            $fields = getDepartment()->departmentFields()->latest()->paginate($perPage);
        }

        return view('member.fields.index', compact('fields'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('member.fields.create');
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
        $requestData['department_id'] = getDepartment()->id;
        
        Field::create($requestData);

        return redirect('member/fields')->with('flash_message', __('admin.field').' '.__('admin.added'));
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

        return view('member.fields.show', compact('field'));
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

        return view('member.fields.edit', compact('field'));
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

        return redirect('member/fields')->with('flash_message', __('admin.field').' '.__('admin.updated'));
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
        return redirect('member/fields')->with('flash_message', __('admin.field').' '.__('admin.deleted'));
    }
}
