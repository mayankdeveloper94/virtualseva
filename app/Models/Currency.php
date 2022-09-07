<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class Currency extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['country_id','is_default','exchange_rate'];

    public function country(){
        return $this->belongsTo(Country::class);
    }

    public function paymentMethods(){
        return $this->belongsToMany(PaymentMethod::class);
    }
}
