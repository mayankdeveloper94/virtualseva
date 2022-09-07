<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Slider;
use Illuminate\Http\Request;

class SlidersController extends Controller
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
            $sliders = Slider::paginate($perPage);
        } else {
            $sliders = Slider::paginate($perPage);
        }

        return view('admin.sliders.index', compact('sliders'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.sliders.create');
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
        $requestData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            // 'image' => 'required|image|max:10000',
        ]);
        
        $slider = '';
        
        // if ($image = $request->file('image')) {
        //     $ImagePath = 'saas_uploads/sliders/';
        //     $ImageFile = time() . "." . $image->getClientOriginalExtension();
        //     $image->move($ImagePath, $ImageFile);
        //     $slider = "$ImageFile";
        // }
        
        // if(!empty($files)){
        //     $rules = [];
        //     foreach($files as $key=>$value){
        //         $rules[$key]='file|max:10000|mimes:jpeg,png,gif';
        //     }
        //     $this->validate($request,$rules);
        // }
        
        // if(!empty($files)){
        //     foreach($files as $key=>$value){
        //         if($request->hasFile($key)){
        //             $path =  $request->file($key)->store('sliders','saas_uploads');
        //             $file = 'saas_uploads/'.$path;
        //             $slider = trim($file);
        //         }
        //     }
        // }
        
        $requestData['image'] = $slider;
   
        $sliderData = Slider::create($requestData);

        return redirect('admin/sliders')->with('flash_message', __('saas.changes-saved'));
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
        $slider = Slider::findOrFail($id);

        return view('admin.sliders.show', compact('slider'));
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
        $slider = Slider::findOrFail($id);

        return view('admin.sliders.edit', compact('slider'));
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
        
        $requestData = $request->validate([
            'title' => 'required|max:255',
            'description' => 'required',
            // 'image' => 'required|image|max:10000',
        ]);
        
        $slider = Slider::findOrFail($id);
        
        $sliderImage = $slider->image;
        
        // if ($image = $request->file('image')) {
        //     $ImagePath = 'saas_uploads/sliders/';
        //     $ImageFile = time() . "." . $image->getClientOriginalExtension();
        //     $image->move($ImagePath, $ImageFile);
        //     $sliderImage = "$ImageFile";
        // }
        
        $requestData['image'] = $sliderImage;
        
        $slider->update($requestData);

        return redirect('admin/sliders')->with('flash_message', __('saas.changes-saved'));
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
        Slider::destroy($id);

        return redirect('admin/sliders')->with('flash_message', __('saas.record-deleted'));
    }
}
