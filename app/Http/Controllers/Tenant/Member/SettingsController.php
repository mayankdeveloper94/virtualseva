<?php

namespace App\Http\Controllers\Tenant\Member;

use App\Lib\HelperTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Tenant\Controller;
use Intervention\Image\Facades\Image;

class SettingsController extends Controller
{

    use HelperTrait;

    public function general(){
        $department = getDepartment();

        return view('member.settings.general',compact('department'));
    }

    public function removePicture(){
        $dept = getDepartment();
        @unlink($dept->picture);
        $dept->picture = null;
        $dept->save();
        return back()->with('flash_message',__('admin.picture').' '.__('admin.deleted'));
    }


    public function saveSettings(Request $request){
        $this->validate($request,[
            'name'=>'required',
            'picture' => 'file|max:10000|mimes:jpeg,png,gif',
        ]);

        $requestData = $request->all();

        $department = getDepartment();

        if($request->hasFile('picture')){
            @unlink($department->picture);

            $path =  $request->file('picture')->store(DEPARTMENTS,'public_uploads');

            $file = 'uploads/'.$path;
            $img = Image::make($file);

            $img->resize(500, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->save($file);

            $requestData['picture'] = $file;
        }

        $department->update($requestData);



        return back()->with('flash_message', __('admin.department').' '.__('admin.updated'));

    }





}
