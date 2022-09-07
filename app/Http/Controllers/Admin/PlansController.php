<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Package;
use Illuminate\Http\Request;

class PlansController extends Controller
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
            $plans = Package::latest()->paginate($perPage);
        } else {
            $plans = Package::latest()->paginate($perPage);
        }

        return view('admin.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.plans.create');
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
            'storage_unit'=>'required'
        ]);
        
        $requestData = $request->all();

        //Calculate storage space
      /*  $unit = $requestData['unit'];
        $limit = $requestData['storage_space'];
        switch($unit){
            case 'mb':
                $requestData['storage_space'] = $limit* 1048576;
                break;

        }*/



       $package= Package::create($requestData);

       //create new package durations
       $package->packageDurations()->createMany([
           [
               'type'=>'m',
               'seconds'=> (86400*30),
               'price'=>$requestData['monthly_price'],
               'stripe_plan'=>$requestData['stripe_plan_m']
           ],
           [
               'type'=>'a',
               'seconds'=> (86400*365),
               'price'=>$requestData['annual_price'],
               'stripe_plan'=>$requestData['stripe_plan_a']
           ]
       ]);

        return redirect('admin/plans')->with('flash_message', __('saas.changes-saved'));
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
        $plan = Package::findOrFail($id);

        return view('admin.plans.show', compact('plan'));
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
        $plan = Package::findOrFail($id);

        $monthly_price = $plan->packageDurations()->where('type','m')->first()->price;
        $annual_price = $plan->packageDurations()->where('type','a')->first()->price;

        $monthlyDuration = $plan->packageDurations()->where('type','m')->first();
        $annualDuration = $plan->packageDurations()->where('type','a')->first();

        return view('admin.plans.edit', compact('plan','monthly_price','annual_price','monthlyDuration','annualDuration'));
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
            'storage_unit'=>'required'
        ]);


        $requestData = $request->all();
        
        $plan = Package::findOrFail($id);
        $plan->update($requestData);

        $monthlyDuration =$plan->packageDurations()->where('type','m')->first();
        if($monthlyDuration){
            $monthlyDuration->price = $requestData['monthly_price'];
            $monthlyDuration->stripe_plan = $requestData['stripe_plan_m'];
            $monthlyDuration->save();
        }

        $annualDuration= $plan->packageDurations()->where('type','a')->first();
        if($annualDuration){
            $annualDuration->price = $requestData['annual_price'];
            $annualDuration->stripe_plan = $requestData['stripe_plan_a'];
            $annualDuration->save();
        }

        return redirect('admin/plans')->with('flash_message',  __('saas.changes-saved'));
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
        Package::destroy($id);

        return redirect('admin/plans')->with('flash_message', 'Package deleted!');
    }
}
