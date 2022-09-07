<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\AboutUs;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    
    public function edit()
    {
        $AboutUs = AboutUs::first();

        return view('admin.about_us.edit', compact('AboutUs'));
    }

    public function update(Request $request)
    {
        $data = $request->validate(['content' => 'required']);
        
        $AboutUs = AboutUs::first();
        
        if($AboutUs){
            $AboutUs->update($data);
        }else{
            AboutUs::create(['content' =>  $request->content]);
        }

        return redirect('admin/about-us')->with('flash_message', __('saas.changes-saved'));
    }

}
