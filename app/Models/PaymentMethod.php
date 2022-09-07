<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class PaymentMethod extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['name','status','code','sort_order','options','class','method_label','translate','is_global'];

    public function paymentMethodFields(){
        return $this->hasMany(PaymentMethodField::class);
    }

    public function currencies(){
        return $this->belongsToMany(Currency::class);
    }
}
