<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Advertisement;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class AdvertisementsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request){
        $advertisements = Advertisement::with('analytics')->orderBy('id')->paginate(10);	
        $id = $advertisements['campid'];
        return view('admin.advertisements.index', compact('advertisements', 'id'));
    }
    
    public function show(Request $request, $id){
        $advertisements = Advertisement::where('campid', $id)->with('analytics')->orderBy('id')->paginate(10);	
        return view('admin.advertisements.index', compact('advertisements', 'id'));
    }
	
	public function create($id){
        return view('admin.advertisements.create', compact('id'));
    }
    
	public function store(Request $request){
	    $this->validate($request,[
            'campid'=>'required',
            'title'=>'required|unique:advertisements,title',            
            'website_url'=>'required',
            'description'=>'required',
			'image' => 'file|max:10000|mimes:jpeg,png,gif'
        ]);
        
		$requestData = $request->all();
		$campid = $requestData["campid"];
		$files 		 = $request->files->all();
        if(!empty($files)){
            foreach($files as $key=>$value){
				
				$path =  $request->file($key)->store(ADS,'public_uploads');
                $file = 'uploads/'.$path;
			
			}
		}       
		
		unset($requestData['image']);
		
		$arr1 = array_merge($requestData,array("image" => $file));
		
		Advertisement::create($arr1);
		
        return redirect('admin/advertisements/'.$campid)->with('flash_message', 'Advertisement '.__('admin.added'));
		
    }
	
	public function destroy($id){
		$advertisements = Advertisement::findOrFail($id);	
		$campId = $advertisements['campid'];
		@unlink($advertisements->image);
		
		Advertisement::destroy($id);		
		
        return redirect('admin/advertisements/'.$campId)->with('flash_message', 'Advertisement '.__('admin.deleted'));
    }
	
	public function edit($id)
    {
		$advertisements = Advertisement::findOrFail($id);		

        return view('admin.advertisements.edit', compact('advertisements'));
    }
	
	public function removeimage($id){
		
		$advertisements = Advertisement::findOrFail($id);		
		@unlink($advertisements->image);
		
		$advertisements->image = null;
		$advertisements->save();
		return back()->with('flash_message',__('admin.picture-removed'));
		
		
	}

	public function update(Request $request, $id)
    {
		
		$this->validate($request,[
            'title'=>'required',            
            'website_url'=>'required',
            'description'=>'required',
			'image' => 'file|max:10000|mimes:jpeg,png,gif'
        ]);
        
		$requestData = $request->all();
		$files 		 = $request->files->all();
		
		if(!empty($files)){
			
			$advertisements = Advertisement::findOrFail($id);		
			@unlink($advertisements->image);
			
            foreach($files as $key=>$value){
				
				$path =  $request->file($key)->store(ADS,'public_uploads');
                $file = 'uploads/'.$path;
				
			}
		}  
		else{
			$advertisements = Advertisement::findOrFail($id);
			$file 			 = $advertisements->image;
		}
		
		unset($requestData['image']);
		$arr1 = array_merge($requestData,array("image" => $file));
		
		
		$advertisements = Advertisement::findOrFail($id);
		$advertisements->update($arr1);
		$campId = $advertisements['campid'];
		return redirect('admin/advertisements/'.$campId)->with('flash_message', 'Advertisement '.__('admin.updated'));
		
	}		
	
}
