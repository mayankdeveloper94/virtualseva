<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\AboutUs;
use Illuminate\Http\Request;

class AboutUsController extends Controller
{
    
    public function edit()
    {
        $AboutUs = AboutUs::first();

        return view('admin.AboutUs.edit', compact('AboutUs'));
    }

    public function update(Request $request)
    {
        $data = $request->validate(['description' => 'required']);
        
        $AboutUs = AboutUs::first();
        
        if($AboutUs){
            $AboutUs->update($data);
        }else{
            AboutUs::create(['description' =>  $request->description]);
        }

        return redirect('admin/about-us')->with('flash_message', __('saas.changes-saved'));
    }

}
