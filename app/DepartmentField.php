<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DepartmentField extends Model
{
    protected $guarded = ['id'];

    public function department(){
        return $this->belongsTo(Department::class);
    }

    public function users(){
        return $this->belongsToMany(User::class);
    }

}
