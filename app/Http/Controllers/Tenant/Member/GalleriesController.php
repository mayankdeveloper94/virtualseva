<?php

namespace App\Http\Controllers\Tenant\Member;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Gallery;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;

class GalleriesController extends Controller
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
            $galleries = getDepartment()->galleries()->whereRaw("match(name,description) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
        } else {
            $galleries = getDepartment()->galleries()->latest()->paginate($perPage);
        }

        return view('member.galleries.index', compact('galleries'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('member.galleries.create');
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
            'picture' => 'required|file|max:10000|mimes:jpeg,png,gif',
        ]);

        $requestData = $request->all();

        if($request->hasFile('picture')){

            $path =  $request->file('picture')->store(GALLERIES,'public_uploads');

            $file = 'uploads/'.$path;
            $img = Image::make($file);

            $img->resize(800, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($file);

            $requestData['file_path'] = $file;
        }
        $requestData['department_id'] = getDepartment()->id;
        Gallery::create($requestData);

        return redirect('member/galleries')->with('flash_message', __('admin.changes-saved'));
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
        $gallery = Gallery::findOrFail($id);

        return view('member.galleries.show', compact('gallery'));
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
        $gallery = Gallery::findOrFail($id);

        return view('member.galleries.edit', compact('gallery'));
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
            'picture' => 'required|file|max:10000|mimes:jpeg,png,gif',
        ]);

        $requestData = $request->all();

        $gallery = Gallery::findOrFail($id);

        if($request->hasFile('picture')){
            @unlink($gallery->file_path);

            $path =  $request->file('picture')->store(GALLERIES,'public_uploads');

            $file = 'uploads/'.$path;
            $img = Image::make($file);

            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($file);

            $requestData['file_path'] = $file;
        }
        else{
            $requestData['file_path'] = $gallery->file_path;
        }

        $gallery->update($requestData);

        return redirect('member/galleries')->with('flash_message',  __('admin.changes-saved'));
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
        $gallery = Gallery::find($id);
        @unlink($gallery->file_path);
        Gallery::destroy($id);

        return redirect('member/galleries')->with('flash_message',  __('admin.deleted'));
    }


}
