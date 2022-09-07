<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class PackageDuration extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['package_id','type','seconds','price','stripe_plan'];

    public function package(){
        return $this->belongsTo(Package::class);
    }

}
