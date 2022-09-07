<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Carouselslider;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class CarouselslidersController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        
		$carouselsliders = Carouselslider::orderBy('id')->paginate(10);	
		
        return view('admin.carouselsliders.index', compact('carouselsliders'));
    }
	
	public function create()
    {
        return view('admin.carouselsliders.create');
    }
	
	public function store(Request $request)
    {
			
        $this->validate($request,[
            'caption1'=>'required',            
            'caption2'=>'required',
// 			'sliderimage' => 'file|max:10000|mimes:jpeg,png,gif|dimensions:min_width=700,min_height=400,max_width=700,max_height=400',
			'sliderimage' => 'file|max:10000|mimes:jpeg,png,gif'
        ]);
        
		$requestData = $request->all();
		$files 		 = $request->files->all();
        
		if(!empty($files)){
            foreach($files as $key=>$value){
				
				$path =  $request->file($key)->store(SETTINGS,'public_uploads');
                $file = 'uploads/'.$path;
				
				$img = Image::make($file);

                $img->resize(700, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                });
				
                $img->save($file);			
				
			}
		}       
		
		unset($requestData['sliderimage']);
		$arr1 = array_merge($requestData,array("sliderimage" => $file));
		
		Carouselslider::create($arr1);
		
		
        return redirect('admin/carouselsliders')->with('flash_message', __('admin.carouselslider').' '.__('admin.added'));
		
		
    }
	
	public function destroy($id)
    {
        
		$carouselsliders = Carouselslider::findOrFail($id);		
		@unlink($carouselsliders->sliderimage);
		
		Carouselslider::destroy($id);		
		
        return redirect('admin/carouselsliders')->with('flash_message', __('admin.carouselslider').' '.__('admin.deleted'));
    }
	
	public function edit($id)
    {
		$carouselsliders = Carouselslider::findOrFail($id);		

        return view('admin.carouselsliders.edit', compact('carouselsliders'));
    }
	
	public function removeimage($id){
		
		$carouselsliders = Carouselslider::findOrFail($id);		
		@unlink($carouselsliders->sliderimage);
		
		$carouselsliders->sliderimage = null;
		$carouselsliders->save();
		return back()->with('flash_message',__('admin.picture-removed'));
		
		
	}

	public function update(Request $request, $id)
    {
		
		$this->validate($request,[
            'caption1'=>'required',            
            'caption2'=>'required',
			'sliderimage' => 'file|max:10000|mimes:jpeg,png,gif'
        ]);
        
		$requestData = $request->all();
		$files 		 = $request->files->all();
		
		if(!empty($files)){
			
			$carouselsliders = Carouselslider::findOrFail($id);		
			@unlink($carouselsliders->sliderimage);
			
            foreach($files as $key=>$value){
				
				$path =  $request->file($key)->store(SETTINGS,'public_uploads');
                $file = 'uploads/'.$path;
				
				$img = Image::make($file);

                $img->resize(700, null, function ($constraint) {
                        $constraint->aspectRatio();
                        $constraint->upsize();
                });
				
                $img->save($file);			
				
			}
		}  
		else{
			$carouselsliders = Carouselslider::findOrFail($id);
			$file 			 = $carouselsliders->sliderimage;
		}
		
		unset($requestData['sliderimage']);
		$arr1 = array_merge($requestData,array("sliderimage" => $file));
		
		
		$carouselsliders = Carouselslider::findOrFail($id);
		$carouselsliders->update($arr1);
		
		return redirect('admin/carouselsliders')->with('flash_message', __('admin.carouselslider').' '.__('admin.updated'));
		
	}		
	
}
