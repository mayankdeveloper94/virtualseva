<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;

use App\Models\Hostname;
use App\Models\Website;
use Hyn\Tenancy\Contracts\Repositories\HostnameRepository;


use Illuminate\Http\Request;

class HostnamesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request,Website $website)
    {
        $keyword = $request->get('search');
        $perPage = 25;


        $hostnames = $website->hostnames()->latest()->paginate($perPage);

        return view('admin.hostnames.index', compact('hostnames','website'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create(Website $website)
    {
        return view('admin.hostnames.create',compact('website'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function store(Request $request,Website $website)
    {
        $this->validate($request,[
           'fqdn'=>'required|unique:hostnames|min:3|max:200'
        ]);
        $requestData = $request->all();

      //  $requestData['website_id'] = $website->id;
    ///    Hostname::create($requestData);

        $hostname = new Hostname();


        $url  = strtolower($request->fqdn);
        $url = str_ireplace('https://','',$url);
        $url = str_ireplace('http://','',$url);

        $hostname->fqdn = $url;
        $hostname->force_https = $request->force_https;
        $hostname->redirect_to = $request->redirect_to;
        $hostname = app(HostnameRepository::class)->create($hostname);
        app(HostnameRepository::class)->attach($hostname, $website);

        return redirect()->route('admin.hostnames.index',['website'=>$website->id])->with('flash_message', __('saas.changes-saved'));
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
        $hostname = Hostname::findOrFail($id);

        return view('admin.hostnames.show', compact('hostname'));
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
        $hostname = Hostname::findOrFail($id);

        return view('admin.hostnames.edit', compact('hostname'));
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
        $hostname = Hostname::find($id);

        if($request->fqdn != $hostname->fqdn){
            $this->validate($request,[
                'fqdn'=>'required|unique:hostnames|min:3|max:200'
            ]);
        }
        else{
            $this->validate($request,[
                'fqdn'=>'required|min:3|max:200'
            ]);
        }


        $requestData = $request->all();
        
        $hostname = Hostname::findOrFail($id);
        $url  = strtolower($request->fqdn);
        $url = str_ireplace('https://','',$url);
        $url = str_ireplace('http://','',$url);
        $requestData['fqdn'] = $url;
        $hostname->update($requestData);

        return redirect()->route('admin.hostnames.index',['website'=>$hostname->website->id])->with('flash_message', __('saas.changes-saved'));
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
        $websiteId = Hostname::find($id)->website_id;
        Hostname::destroy($id);

        return redirect()->route('admin.hostnames.index',['website'=>$websiteId])->with('flash_message', __('saas.record-deleted'));
    }
}
