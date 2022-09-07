<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class BillingAddress extends Model
{
    use UsesSystemConnection;
    protected $fillable =['user_id','name','address','address_2','city','state','zip','country_id','is_default','phone'];

    public function country(){
        return $this->belongsTo(Country::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

}
