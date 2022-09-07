<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class PaymentMethodField extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['key','value','serialized','type','options','class','payment_method_id'];

    public function paymentMethod(){
        return $this->belongsTo(PaymentMethod::class);
    }
}
