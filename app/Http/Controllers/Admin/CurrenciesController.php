<?php

namespace App\Http\Controllers\Admin;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrenciesController extends Controller
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
            $currencies = Currency::latest()->paginate($perPage);
        } else {
            $currencies = Currency::latest()->paginate($perPage);
        }

        return view('admin.currencies.index', compact('currencies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.currencies.create');
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
           'country_id'=>'required',
            'exchange_rate'=>'required|numeric'
        ]);
        $requestData = $request->all();

        if($requestData['is_default']==1){
            Currency::where('id','>',0)->update(['is_default'=>0]);
        }
        
        Currency::create($requestData);

        return redirect('admin/currencies')->with('flash_message', __('saas.changes-saved'));
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
        $currency = Currency::findOrFail($id);

        return view('admin.currencies.show', compact('currency'));
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
        $currency = Currency::findOrFail($id);

        return view('admin.currencies.edit', compact('currency'));
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
            'country_id'=>'required',
            'exchange_rate'=>'required|numeric'
        ]);
        $requestData = $request->all();

        if($requestData['is_default']==1){
            Currency::where('id','>',0)->update(['is_default'=>0]);
        }
        
        $currency = Currency::findOrFail($id);
        $currency->update($requestData);

        return redirect('admin/currencies')->with('flash_message', __('saas.changes-saved'));
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
        Currency::destroy($id);

        return redirect('admin/currencies')->with('flash_message', __('saas.record-deleted'));
    }
}
