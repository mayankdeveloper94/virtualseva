<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    protected $fillable = ['name','description','file_path','department_id'];

    public function department(){
        return $this->belongsTo(Department::class);
    }
}
