<?php

namespace App\Http\Controllers\Tenant\Member;

use App\Http\Requests;
use App\Http\Controllers\Tenant\Controller;

use App\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TeamsController extends Controller
{


    public function __construct()
    {

        $this->middleware('department.admin')->except('show','myTeams');
    }

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
            $teams = getDepartment()->teams()->where('name','LIKE',"%{$keyword}%")->paginate($perPage);
        } else {
            $teams = getDepartment()->teams()->orderBy('name')->paginate($perPage);
        }

        return view('member.teams.index', compact('teams'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {

        $members = getDepartment()->users()->orderBy('name')->limit(5000)->get();
        return view('member.teams.create',['members'=>$members]);
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
           'name'=>'required'
        ]);

        $requestData = $request->all();
        $requestData['department_id'] = getDepartment()->id;

        $team= Team::create($requestData);
        $team->users()->attach($requestData['members']);


        return redirect('member/teams')->with('flash_message', __('admin.team').' '.__('admin.added').' !');
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

        $team = Team::findOrFail($id);
        $this->authorize('view',$team);

        return view('member.teams.show', compact('team'));
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

        $team = Team::findOrFail($id);
        $this->authorize('update',$team);
        $members = getDepartment()->users()->orderBy('name')->limit(5000)->get();
        return view('member.teams.edit', compact('team','members'));
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
        
        $team = Team::findOrFail($id);
        $this->authorize('update',$team);
        $team->update($requestData);

        $team->users()->sync($requestData['members']);

        return redirect('member/teams')->with('flash_message', 'Team updated!');
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
        $this->authorize('delete',Team::find($id));
        Team::destroy($id);

        return redirect('member/teams')->with('flash_message', 'Team deleted!');
    }

    public function myTeams(Request $request){

        $user = Auth::user();
        $keyword = $request->get('search');
        $perPage = 25;
        $did = getDepartment()->id;

        if (!empty($keyword)) {
            $teams = $user->teams()->where('department_id',$did)->where('name','LIKE',"%{$keyword}%")->paginate($perPage);
        } else {
            $teams = $user->teams()->where('department_id',$did)->orderBy('name')->paginate($perPage);
        }

        return view('member.teams.my_teams',compact('teams'));
    }
}
