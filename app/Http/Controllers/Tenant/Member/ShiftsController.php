<?php

namespace App\Http\Controllers\Tenant\Member;

use App\Event;
use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Shift;
use Illuminate\Http\Request;

class ShiftsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request,Event $event)
    {
        $this->authorize('view',$event);
        $perPage = 25;


        $shifts = $event->shifts()->orderBy('starts')->paginate($perPage);


        return view('member.shifts.index', compact('shifts','event'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(Event $event)
    {
        $this->authorize('view',$event);
        $members = getDepartment()->users()->orderBy('name')->limit(5000)->get();
        return view('member.shifts.create',compact('event','members'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request,Event $event)
    {
        $this->validate($request,[
           'name'=>'required',
            'starts_submit'=>'required',
            'ends_submit'=>'required'
        ]);

        $this->authorize('view',$event);

        $requestData = $request->all();
        $requestData['event_id']= $event->id;
        $requestData['starts']= $requestData['starts_submit'];
        $requestData['ends'] = $requestData['ends_submit'];


        $shift= Shift::create($requestData);

        $shift->users()->attach($requestData['members']);

        return redirect()->route('member.shifts.index',['event'=>$event->id])->with('flash_message', __('admin.shift').' '.__('admin.added'));
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
        $shift = Shift::findOrFail($id);
        $this->authorize('view',$shift);

        return view('member.shifts.show', compact('shift'));
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
        $shift = Shift::findOrFail($id);
        $this->authorize('update',$shift);
        $members = getDepartment()->users()->orderBy('name')->limit(5000)->get();

        return view('member.shifts.edit', compact('shift','members'));
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
            'starts'=>'required',
            'ends'=>'required'
        ]);

        $requestData = $request->all();
        $requestData['starts']= $requestData['starts_submit'];
        $requestData['ends'] = $requestData['ends_submit'];
        
        $shift = Shift::findOrFail($id);
        $this->authorize('update',$shift);
        $shift->update($requestData);

        $shift->users()->sync($requestData['members']);

        return redirect()->route('member.shifts.index',['event'=>$shift->event->id])->with('flash_message', __('admin.changes-saved'));
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
        $this->authorize('delete',Shift::find($id));
        $event= Shift::find($id)->event;

        Shift::destroy($id);

        return redirect()->route('member.shifts.index',['event'=>$event->id])->with('flash_message', __('admin.deleted'));
    }

    public function tasks(Shift $shift){

        return view('member.shifts.tasks', compact('shift'));
    }

    public function saveTasks(Request $request,Shift $shift){

        $data = $request->all();
        foreach($shift->users as $user){
            $value = $data[$user->id];
            $user->shifts()->updateExistingPivot($shift->id,['tasks'=>$value]);
        }

        return redirect()->route('member.shifts.index',['event'=>$shift->event->id])->with('flash_message', __('admin.changes-saved'));
    }




}
