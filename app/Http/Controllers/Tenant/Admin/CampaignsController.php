<?php

namespace App\Http\Controllers\Tenant\Admin;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Campaign;
use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\DB;
class CampaignsController extends Controller{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request){
		//$campaigns = Campaign::orderBy('id')->paginate(10);
		$table = DB::table('campaigns')
        ->leftJoin('advertisements', 'campaigns.id', '=', 'advertisements.campid')
        ->leftJoin('analytics', 'advertisements.id', '=', 'analytics.advertisement_id')// you may add more joins
        ->select('campaigns.*', 'analytics.no_of_clicks');
        $campaigns = $table->groupBy('campaigns.id')->paginate(10);
		return view('admin.campaign.index', compact('campaigns'));
    }
    
    public function earning(Request $request){
		$table = DB::table('analytics')
        ->leftJoin('advertisements', 'analytics.advertisement_id', '=', 'advertisements.id')
        ->leftJoin('campaigns', 'advertisements.campid', '=', 'campaigns.id')
        ->leftJoin('users', 'analytics.user_id', '=', 'users.id')
        ->select('analytics.*', 'advertisements.title as adstitle', 'campaigns.title as camptitle', 'users.name as usertitle');
        $campaigns = $table->groupBy('analytics.id')->paginate(10);
        //dd($campaigns);
		return view('admin.campaign.earning', compact('campaigns'));
    }
	
	public function create(){
        return view('admin.campaign.create');
    }
	
	public function store(Request $request){
        $this->validate($request,[
            'title'=>'required',            
            'description'=>'required',
			'image' => 'file|max:10000|mimes:jpeg,png,gif'
        ]);
		$requestData = $request->all();
		$files 		 = $request->files->all();
        if(!empty($files)){
            foreach($files as $key=>$value){
				$path =  $request->file($key)->store(ADS,'public_uploads');
                $file = 'uploads/'.$path;
			}
		}       
		unset($requestData['image']);
		$arr1 = array_merge($requestData,array("image" => $file));
		Campaign::create($arr1);
		return redirect('admin/campaign')->with('flash_message', 'Campaign '.__('admin.added'));
    }
	
	public function destroy($id){
		$campaigns = Campaign::findOrFail($id);	
		Campaign::destroy($id);		
        return redirect('admin/campaign')->with('flash_message', 'Campaign '.__('admin.deleted'));
    }
	
	public function edit($id){
		$campaigns = Campaign::findOrFail($id);		
        return view('admin.campaign.edit', compact('campaigns'));
    }
    
    public function removeimage($id){
		$campaigns = Campaign::findOrFail($id);		
		@unlink($campaigns->image);
		$campaigns->image = null;
		$campaigns->save();
		return back()->with('flash_message',__('admin.picture-removed'));
	}

	public function update(Request $request, $id){
		$this->validate($request,[
            'title'=>'required'
        ]);
		$requestData = $request->all();
		$files 		 = $request->files->all();
		if(!empty($files)){
			$campaigns = Campaign::findOrFail($id);		
			@unlink($campaigns->image);
			
            foreach($files as $key=>$value){
				$path =  $request->file($key)->store(ADS,'public_uploads');
                $file = 'uploads/'.$path;
			}
		}else{
			$campaigns = Campaign::findOrFail($id);
			$file 			 = $campaigns->image;
		}
		unset($requestData['image']);
		$arr1 = array_merge($requestData,array("image" => $file));
		$campaigns = Campaign::findOrFail($id);
		$campaigns->update($arr1);
		return redirect('admin/campaign')->with('flash_message', 'Campaign '.__('admin.updated'));
	}
}