<?php

namespace App\Http\Controllers\Tenant\Member;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Event;
use App\Rejection;
use App\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

class EventsController extends Controller
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
            $events = getDepartment()->events()->whereRaw("match(name,venue,description) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
        } else {
            $events = getDepartment()->events()->orderBy('event_date','desc')->paginate($perPage);
        }

        $controller = $this;
        return view('member.events.index', compact('events','controller'));
    }


    public function getTotalUsers($event){

        $users = [];
        foreach($event->shifts as $shift){
            foreach($shift->users as $user){
                $users[$user->id]=$user->id;
            }

        }
        return count($users);
    }
    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('member.events.create');
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
            'event_date'=>'required'
        ]);
        
        $requestData = $request->all();

        $requestData['department_id'] = getDepartment()->id;
        
       $event= Event::create($requestData);

        return redirect()->route('member.shifts.create',['event'=>$event->id])->with('flash_message', __('admin.event').' '.__('admin.saved').'. '.__('site.create-new').' '.__('admin.shift'));
      //  return redirect('member/events')->with('flash_message', __('admin.event').' '.__('admin.saved'));
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
        $event = Event::findOrFail($id);
        $this->authorize('view',$event);

        return view('member.events.show', compact('event'));
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
        $event = Event::findOrFail($id);
        $this->authorize('update',$event);

        return view('member.events.edit', compact('event'));
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
            'event_date'=>'required'
        ]);
        $requestData = $request->all();
        
        $event = Event::findOrFail($id);
        $this->authorize('update',$event);
        $event->update($requestData);

        return redirect('member/events')->with('flash_message', __('admin.changes-saved'));
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
        $this->authorize('delete',Event::find($id));
        Event::destroy($id);

        return redirect('member/events')->with('flash_message', __('admin.event').' '.__('admin.deleted'));
    }

    public function roster(Request $request){

        $start = $request->get('start');
        $end = $request->get('end');
        $perPage = 25;

        if (!empty($start) || !empty($end)) {
            $events = getDepartment()->events()->orderBy('event_date');
            if(!empty($start)){
                $events= $events->where('event_date','>=',Carbon::parse($start)->toDateTimeString());
            }

            if(!empty($end)){
                $events= $events->where('event_date','<',Carbon::parse($end)->toDateTimeString());
            }

            $events = $events->orderBy('event_date')->paginate($perPage);
        } else {
            $events = getDepartment()->events()->where('event_date' , '>=' , Carbon::yesterday()->toDateTimeString())->orderBy('event_date')->paginate($perPage);
        }


        return view('member.events.roster', compact('events','start','end'));
    }

    public function optOut(Request $request,Shift $shift){
        $this->validate($request,[
            'message'=>'required'
        ]);

        $rejection = Rejection::create([
            'shift_id'=>$shift->id,
            'user_id'=>Auth::user()->id,
            'message'=>$request->message
        ]);

        $shift->users()->detach(Auth::user()->id);
        return back()->with('flash_message',__('admin.changes-saved'));
    }

    public function shifts(Request $request){
        $user = Auth::user();
        $start = $request->get('start');
        $end = $request->get('end');
        $perPage = 25;
        $department=getDepartment();
        if (!empty($start) || !empty($end)) {


            $shifts=  $user->shifts()->whereHas('event',function($q) use($department,$start,$end){
                $q->where('department_id',$department->id);
                if(!empty($start)){
                    $q->where('event_date','>=',Carbon::parse($start)->toDateTimeString());
                }

                if(!empty($end)){
                    $q->where('event_date','<',Carbon::parse($end)->toDateTimeString());
                }
                $q->orderBy('event_date');

            })->paginate($perPage);

        } else {

          $shifts=  $user->shifts()->whereHas('event',function($q) use($department){
                $q->where('department_id',$department->id);
                $q->where('event_date' , '>=' , Carbon::yesterday()->toDateTimeString())->orderBy('event_date');
            })->paginate($perPage);

     //       $events = getDepartment()->events()->where('event_date' , '>=' , Carbon::yesterday()->toDateTimeString())->orderBy('event_date')->paginate($perPage);
        }

        return view('member.events.shifts', compact('shifts','start','end'));
    }

}
