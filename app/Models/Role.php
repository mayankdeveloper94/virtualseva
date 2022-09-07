<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class Role extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['id','title'];


    public function users(){
        return $this->hasMany(User::class);
    }

}
