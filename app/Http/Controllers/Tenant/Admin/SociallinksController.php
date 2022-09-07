<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Sociallink;
use Illuminate\Http\Request;

class SociallinksController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        
		$sociallinks = Sociallink::orderBy('id')->paginate(10);	
		
        return view('admin.sociallinks.index', compact('sociallinks'));
    }
	
	public function create()
    {
        return view('admin.sociallinks.create');
    }
	
	public function store(Request $request)
    {
			
        $this->validate($request,[
            'icontext'=>'required',            
            'socialurl'=>'required'
        ]);
        $requestData = $request->all();
        
        Sociallink::create($requestData);

        return redirect('admin/sociallinks')->with('flash_message', __('admin.social-links').' '.__('admin.added'));
		
		
    }
	
	public function destroy($id)
    {
        Sociallink::destroy($id);
        return redirect('admin/sociallinks')->with('flash_message', __('admin.social-links').' '.__('admin.deleted'));
    }
	
	public function edit($id)
    {
		$sociallink = Sociallink::findOrFail($id);		

        return view('admin.sociallinks.edit', compact('sociallink'));
    }
	
	public function update(Request $request, $id)
    {
        $this->validate($request,[
            'icontext'=>'required',            
            'socialurl'=>'required'
        ]);
        
        $requestData = $request->all();
        
        $sociallink = Sociallink::findOrFail($id);
        $sociallink->update($requestData);

        return redirect('admin/sociallinks')->with('flash_message', __('admin.social-links').' '.__('admin.updated'));
    }
	
}
