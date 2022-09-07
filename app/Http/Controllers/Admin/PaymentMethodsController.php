<?php

namespace App\Http\Controllers\Admin;

use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class PaymentMethodsController extends Controller
{
    public function index(){

        $paymentMethods = PaymentMethod::orderBy('name')->get();
        return view('admin.payment-methods.index',compact('paymentMethods'));
    }

    public function edit(PaymentMethod $paymentMethod)
    {
        $title = __('saas.edit').' '.$paymentMethod->name;
        if($paymentMethod->translate==1){
            $title = __('saas.edit').' '.__('saas.'.$paymentMethod->code);
        }
        return view('admin.payment-methods.edit',compact('title','paymentMethod'));
    }

    public function update(Request $request,PaymentMethod $paymentMethod){
        $this->validate($request,[
           'method_label'=>'required'
        ]);

        $requestData = $request->all();

        $paymentMethod->fill($requestData);
        $paymentMethod->save();

        $paymentMethod->currencies()->sync($request->currencies);

        foreach($paymentMethod->paymentMethodFields as $field){
            $field->value = $requestData[$field->key];
            $field->save();
        }

        return redirect()->route('admin.payment-methods')->with('flash_message',__('saas.changes-saved'));
    }


}
