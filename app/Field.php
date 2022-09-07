<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{
    protected $guarded = ['id'];

    public function users(){
        return $this->belongsToMany(User::class)->withPivot('value');
    }

}
