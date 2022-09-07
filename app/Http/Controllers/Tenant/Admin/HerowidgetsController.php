<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Herowidget;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class HerowidgetsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
	 
    public function index(Request $request)
    {
        
		$herowidgets = Herowidget::orderBy('id')->paginate(10);	
		
        return view('admin.herowidgets.index', compact('herowidgets'));
    }
	
	public function create()
    {
        return view('admin.herowidgets.create');
    }
	
	public function store(Request $request)
    {
		$image_videoembed = $request->input('image_videoembed');
		
		if($image_videoembed=="1"){		
		
			$this->validate($request,[
				'alignment'=>'required',            
				'title'=>'required',
				'upload_image' => 'file|max:10000|mimes:jpeg,png,gif',
				'description1'=>'required',
			]);
			
		}
		else{
			$this->validate($request,[
				'alignment'=>'required',            
				'title'=>'required',				
				'description1'=>'required',
			]);
		}		
        
		$requestData = $request->all();
		$files 		 = $request->files->all();
        $file		 = '';
		
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
		
		unset($requestData['upload_image']);
		
		if($file!=""){
			$arr1 = array_merge($requestData,array("upload_image" => $file));
		}
		else{
			$arr1 = $requestData;
		}	
		
		Herowidget::create($arr1);
		
		
        return redirect('admin/herowidgets')->with('flash_message', __('admin.herowidgets').' '.__('admin.added'));		
		
    }
	
	public function destroy($id)
    {
        
		$herowidgets = Herowidget::findOrFail($id);		
		@unlink($herowidgets->upload_image);
		
		Herowidget::destroy($id);		
		
        return redirect('admin/herowidgets')->with('flash_message', __('admin.herowidgets').' '.__('admin.deleted'));
    }
	
	public function removeimage($id){
		
		$herowidget = Herowidget::findOrFail($id);		
		@unlink($herowidget->upload_image);
		
		$herowidget->upload_image = null;
		$herowidget->save();
		return back()->with('flash_message',__('admin.picture-removed'));
		
		
	}
	
	public function edit($id)
    {
		$herowidget = Herowidget::findOrFail($id);	

        return view('admin.herowidgets.edit', compact('herowidget'));
    }
	
	public function update(Request $request, $id)
    {
		
		$this->validate($request,[
            'alignment'=>'required',            
            'title'=>'required',
			'upload_image' => 'file|max:10000|mimes:jpeg,png,gif',
			'description1'=>'required',
        ]);
        
		$requestData = $request->all();
		$files 		 = $request->files->all();
		
		if(!empty($files)){
			
			$herowidget = Herowidget::findOrFail($id);		
			@unlink($herowidget->upload_image);
			
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
			$herowidget 	 = Herowidget::findOrFail($id);
			$file 			 = $herowidget->upload_image;
		}
		
		unset($requestData['upload_image']);
		$arr1 = array_merge($requestData,array("upload_image" => $file));
		
		
		$herowidget = Herowidget::findOrFail($id);
		$herowidget->update($arr1);
		
		return redirect('admin/herowidgets')->with('flash_message', __('admin.herowidgets').' '.__('admin.updated'));
		
	}
	
}
