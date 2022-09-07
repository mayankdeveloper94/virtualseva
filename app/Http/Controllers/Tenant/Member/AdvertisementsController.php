<?php

namespace App\Http\Controllers\Tenant\Member;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Advertisement;
use App\Department;
use Illuminate\Http\Request;

class AdvertisementsController extends Controller{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
		$advertisements = getDepartment()->advertisements()->with('analytics','campaign')->groupby('advertisements.id')->paginate(10);	
		//dd($advertisements);
        return view('member.advertisements.index', compact('advertisements'));
    }
	
	public function create(Advertisement $advertisement){
        $members = getDepartment()->users()->get();
        
        $advertisementUsers = $advertisement->users;
        
        return view('member.advertisements.create', compact('advertisement','members','advertisementUsers'));
    }
	
	public function store(Request $request, Advertisement $advertisement)
    {
        $this->validate($request,[
            'user_id'=>'required|array',            
        ],['user_id.required' => 'Select any sevak']);
        
        $users = $request['user_id'];
        
		if(isset($users) && !empty($users)){
		  $advertisement->users()->sync($users);
		}
		
        return redirect('member/advertisements')->with('flash_message', 'Sevak assigned');
		
    }
	
}
