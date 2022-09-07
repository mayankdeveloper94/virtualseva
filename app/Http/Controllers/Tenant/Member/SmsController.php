<?php

namespace App\Http\Controllers\Tenant\Member;

use App\Department;
use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Lib\HelperTrait;
use App\Lib\SmsGateway;
use App\Sms;
use App\Team;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SmsController extends Controller
{
    use HelperTrait;


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $keyword = $request->get('search');
        $perPage = 25;

        $user = Auth::user();

        if (!empty($keyword)) {
            $sms = $user->sms()->latest()->whereRaw("match(message,notes) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
        } else {
            $sms = $user->sms()->latest()->paginate($perPage);
        }

        return view('member.sms.index', compact('sms'));
    }



    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {

        $teams = getDepartment()->teams()->orderBy('name')->get();

        $perPage = (setting('sms_max_pages')==1)? 160:153;
        $max = setting('sms_max_pages') * $perPage;

        $replyUser = false;
        if($request->user && User::find($request->user))
        {
            $replyUser = User::find($request->user);
        }


        return view('member.sms.create',compact('teams','max','replyUser'));
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
        $perPage = (setting('sms_max_pages')==1)? 160:153;
        $max = setting('sms_max_pages') * $perPage;
        $this->validate($request,[
            'message'=>'required|max:'.$max
        ]);

        $requestData = $request->all();



        if(empty($requestData['members']) && empty($requestData['departments']) && empty($requestData['all_members'])){
            return back()->withInput($requestData)->with('alert-danger',__('admin.recipient-error'));
        }

        $requestData['user_id'] = Auth::user()->id;

        $sms=  Sms::create($requestData);
        //create all recipients

        $recipients = [];

        if(isset($requestData['all_members']) && $requestData['all_members']==1){
            $this->authorize('administer');
            $allMembers = getDepartment()->users;
            foreach($allMembers as $user){
                $recipients[$user->id] = $user->id;
            }
        }
        else {


            if(isset($requestData['members'])){
                //loop through members
                foreach($requestData['members'] as $value){
                    $recipients[$value] = $value;
                }
            }


            if(isset($requestData['teams'])){
                //now loop through departments
                foreach($requestData['teams'] as $value){
                    $team = Team::find($value);

                    foreach($team->users as $user){
                        $recipients[$user->id] = $user->id;
                    }
                }
            }



        }

        $recipients = array_values($recipients);
        $sms->users()->attach($recipients);

        $numbers = [];
        foreach($recipients as $value){
            if(User::find($value)){
                $numbers[] = User::find($value)->telephone;
            }
        }

        try{
            $gateway = new SmsGateway($numbers,$requestData['message']);
            $msg= $gateway->send();
            return redirect('member/sms')->with('flash_message', __('admin.message-sent').': '.$msg);
        }
        catch(\Exception $ex){
            $this->errorMessage($request,$ex->getMessage());
            return back();
        }



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
        $sms = Sms::findOrFail($id);

        return view('member.sms.show', compact('sms'));
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
        $sms = Sms::findOrFail($id);

        return view('member.sms.edit', compact('sms'));
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
        
        $requestData = $request->all();
        
        $sm = Sms::findOrFail($id);
        $sm->update($requestData);

        return redirect('member/sms')->with('flash_message', 'Sms updated!');
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
        $this->authorize('delete',Sms::find($id));
        Sms::destroy($id);

        return redirect('member/sms')->with('flash_message',  __('member.sms').' '.__('admin.deleted'));
    }

    public function deleteMultiple(Request $request){
        $data = $request->all();
        $count = 0;
        foreach($data as $key=>$value){
            $sms = Sms::find($key);

            if($sms){
                $this->authorize('delete',$sms);
                $sms->delete();
                $count++;
            }

        }

        return back()->with('flash_message',"{$count} ".__('admin.deleted'));
    }


    public function inbox(Request $request){
        $keyword = $request->get('search');
        $perPage = 25;

        $user = Auth::user();
        if (!empty($keyword)) {
            $sms = $user->receivedSms()->latest()->whereRaw("match(message,notes) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
        } else {
            $sms = $user->receivedSms()->latest()->paginate($perPage);
        }

        return view('member.sms.inbox', compact('sms'));
    }


    public function destroyInbox($id)
    {

        $user = Auth::user();
        $user->receivedSms()->detach($id);


        return redirect()->route('sms.inbox')->with('flash_message', __('admin.message').' '.__('admin.deleted'));
    }

    public function deleteMultipleInbox(Request $request){
        $user = Auth::user();
        $data = $request->all();
        $count = 0;
        foreach($data as $key=>$value){
            $email = Sms::find($key);

            if($email){
                $user->receivedSms()->detach($key);

                $count++;
            }

        }

        return back()->with('flash_message',"{$count} ".__('admin.deleted'));
    }


}
