<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class Invoice extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['user_id','invoice_purpose_id','amount','paid','item_id','extra','auto','hash','expires','due_date','currency_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function invoicePurpose(){
        return $this->belongsTo(InvoicePurpose::class);
    }

    public function currency(){
        return $this->belongsTo(Currency::class);
    }

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class);
    }
}
