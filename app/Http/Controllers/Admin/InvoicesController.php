<?php

namespace App\Http\Controllers\Admin;


use App\Http\Controllers\Controller;

use App\Lib\HelperTraitSaas;
use App\Lib\InvoiceApprover;
use App\Models\Invoice;
use Illuminate\Http\Request;

class InvoicesController extends Controller
{
    use HelperTraitSaas;
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
            $invoices = Invoice::latest()->where('id','LIKE',"%{$keyword}%")->orWhereHas('user',function($q) use ($keyword) {
                $q->whereRaw("match(name,email) against (? IN NATURAL LANGUAGE MODE)", [$keyword]);
            })->orWhereRaw("match(extra) against (? IN NATURAL LANGUAGE MODE)", [$keyword])->paginate($perPage);
        } else {
            $invoices = Invoice::latest()->paginate($perPage);
        }
        $controller = $this;
        return view('admin.invoices.index', compact('invoices','controller'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        return view('admin.invoices.create');
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
        $rules = [
          'user_id'=>'required',
            'invoice_purpose_id'=>'required',
            'amount'=>'required',
            'currency_id'=>'required'
        ];
        if($request->invoice_purpose_id==1){
            $rules['item_id'] = 'required';
        }

        $this->validate($request,$rules);

        $requestData = $request->all();




        if(!empty($requestData['expires'])){
            $requestData['expires'] = strtotime($requestData['expires']);
        }

        if(!empty($requestData['due_date']))
        {
            $requestData['due_date'] = strtotime($requestData['due_date']);
        }

       $invoice= Invoice::create($requestData);

        if($request->notify==1){
            $message = __('saas.invoice-mail',[
               'name'=> $invoice->user->name,
                'item'=> $this->getInvoiceItemName($invoice->id),
                'price'=> price($invoice->amount,$invoice->currenc_id),
                'link'=> url('/login'),
                'notes'=>$invoice->extra
            ]);
            $subject = __('saas.new-invoice');
            $this->sendEmail($invoice->user->email,$subject,$message);
        }


        return redirect('admin/invoices')->with('flash_message', __('saas.changes-saved'));
    }


    public function approve(Invoice $invoice)
    {
        $invoiceApprover = new InvoiceApprover();
        $invoiceApprover->approve($invoice->id);
        return back()->with('flash_message', __('saas.changes-saved'));
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
        $invoice = Invoice::findOrFail($id);
        $controller = $this;
        return view('admin.invoices.show', compact('invoice','controller'));
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
        $invoice = Invoice::findOrFail($id);

        if(!empty($invoice->expires))
        {
            $invoice->expires =  date('Y-m-d',$invoice->expires);
        }

        if(!empty($invoice->due_date))
        {
            $invoice->due_date =  date('Y-m-d',$invoice->due_date);
        }


        return view('admin.invoices.edit', compact('invoice'));
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
        $rules = [
            'user_id'=>'required',
            'invoice_purpose_id'=>'required',
            'amount'=>'required',
            'currency_id'=>'required'
        ];
        if($request->invoice_purpose_id==1){
            $rules['item_id'] = 'required';
        }

        $this->validate($request,$rules);

        $requestData = $request->all();
        
        $invoice = Invoice::findOrFail($id);
        if(!empty($requestData['expires'])){
            $requestData['expires'] = strtotime($requestData['expires']);
        }

        if(!empty($requestData['due_date']))
        {
            $requestData['due_date'] = strtotime($requestData['due_date']);
        }


        $invoice->update($requestData);

        return redirect('admin/invoices')->with('flash_message', __('saas.changes-saved'));
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
        Invoice::destroy($id);

        return redirect('admin/invoices')->with('flash_message', __('saas.record-deleted'));
    }


}
