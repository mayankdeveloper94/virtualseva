<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Advertisement;
use App\Department;
use Illuminate\Http\Request;


class AdvertisementChannelPartnersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        
		$advertisements = Advertisement::orderBy('id')->paginate(10);	
		
        return view('admin.advertisements.index', compact('advertisements'));
    }
	
	public function create(Advertisement $advertisement)
    {
        $departments = Department::all();
        $advertisementDeparments = $advertisement->departments;
       
        return view('admin.advertisements-departments.create', compact('advertisement','departments','advertisementDeparments'));
    }
	
	public function store(Request $request, Advertisement $advertisement)
    {
        $this->validate($request,[
            'department_id'=>'required|array',            
        ],['department_id.required' => 'Select any channel partner']);
        
        $deartments = $request['department_id'];
        
		if(isset($deartments) && !empty($deartments)){
		  //  foreach($deartments as $departmentId){
		  //      $advertisement->departments()->attach(['department_id' => $departmentId]);
		  //  }
		  $advertisement->departments()->sync($deartments);
		}
		
        return redirect('admin/advertisements')->with('flash_message', 'Channel partner assigned');
		
    }
	
	public function destroy($id)
    {
        
		$advertisements = Advertisement::findOrFail($id);	
		
		@unlink($advertisements->image);
		
		Advertisement::destroy($id);		
		
        return redirect('admin/advertisements')->with('flash_message', 'Advertisement '.__('admin.deleted'));
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
		
		return redirect('admin/advertisements')->with('flash_message', 'Advertisement '.__('admin.updated'));
		
	}		
	
}
