<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\FormField;
use App\FormOption;
use Illuminate\Http\Request;

class FormsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        
		$forms = FormField::orderBy('field_sortorder')->paginate(100);
		
		
		/*
		$keyword = $request->get('search');
        $perPage = 25;

        if (!empty($keyword)) {
            $fields = Field::latest()->where('name','LIKE',"%{$keyword}%")->paginate($perPage);
        } else {
            $fields = Field::latest()->paginate($perPage);
        }
		*/
		
        return view('admin.forms.index', compact('forms'));
    }
	
	public function create()
    {
        return view('admin.forms.create');
    }

    public function store(Request $request)
    {
		
		
			
        $this->validate($request,[
            'field_labelname'=>'required',
            'field_sortorder'=>"integer",
            'field_type'=>'required'
        ]);
        $requestData = $request->all();
        
        FormField::create($requestData);

        return redirect('admin/forms')->with('flash_message', __('admin.field').' '.__('admin.added'));
		
		
    }
	
	public function destroy($id)
    {
        FormField::destroy($id);
        return redirect('admin/forms')->with('flash_message', __('admin.field').' '.__('admin.deleted'));
    }
	
	public function edit($id)
    {
		$forms = FormField::findOrFail($id);		

        return view('admin.forms.edit', compact('forms'));
    }
	
	public function update(Request $request, $id)
    {
        $this->validate($request,[
            'field_labelname'=>'required',
            'field_sortorder'=>"integer",
            'field_type'=>'required'
        ]);
        
        $requestData = $request->all();
        
        $field = FormField::findOrFail($id);
        $field->update($requestData);

        return redirect('admin/forms')->with('flash_message', __('admin.field').' '.__('admin.updated'));
    }
	
	public function show($id)
    {
        $forms = FormField::findOrFail($id);

        return view('admin.forms.show', compact('forms'));
    }
	
	public function options($id){
		
		
		$forms = FormField::findOrFail($id);
		
		/*
		$forms_options = $forms->whereHas('formoptions',function($q) use ($id){
                $q->where('formoptions_fieldrefid',$id);
            });
		*/
		$formoptions = $forms->formoptions;
		
		return view('admin.forms.options', compact('forms','formoptions','id'));
		
		
		
		
	}

	public function deleteoptions(Request $request){
		
		$id 	= $request->id;
		$id1	= $request->id1;
		
		
		FormOption::destroy($id1);
		
		return redirect('admin/options/'.$id)->with('flash_message', __('admin.options').' '.__('admin.deleted'));
		
	}	

	public function addoptions(Request $request){
		
		$id 		= $request->id;
		
		return view('admin.forms.addoptions', compact('id'));
		
	}

	public function saveoptions(Request $request)
    {
		
		$id 		= $request->id;
		
		$this->validate($request,[
            'formoptions_options'=>'required',
            'formoptions_value'=>'required'            
        ]);
		
        $requestData = $request->all();
		
		$result = FormOption::create($requestData);
		
		
// 		print "<pre>";
// 			print_r($result);
// 		print "</pre>";
		
// 		exit(0);
				

        return redirect('admin/options'.'/'.$id)->with('flash_message', __('admin.options').' '.__('admin.added'));
		
	}

	public function editoptions(Request $request){
		
		$id 		= $request->id;
		$id1 		= $request->id1;
		
		$formsoption = FormOption::findOrFail($id1);
		
		return view('admin.forms.editoptions', compact('id','id1','formsoption'));
		
	}	
	
	public function saveeditoptions(Request $request){
		
		$id 		= $request->id;
		$id1 		= $request->id1;
		
		$this->validate($request,[
            'formoptions_options'=>'required',
            'formoptions_value'=>'required'            
        ]);
		
        $requestData = $request->all();
		
		$formsoption = FormOption::findOrFail($id1);
		$formsoption->update($requestData);
		
		return redirect('admin/options'.'/'.$id)->with('flash_message', __('admin.options').' '.__('admin.updated'));
		
	}	
	
}
