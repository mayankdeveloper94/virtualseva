<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class Package extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['name','sort_order','storage_space','storage_unit','user_limit','department_limit','is_free','public'];

    public function packageDurations(){
        return $this->hasMany(PackageDuration::class);
    }

    public function subscribers(){
        return $this->hasManyThrough(Subscriber::class,PackageDuration::class);
    }

}
