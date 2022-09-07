<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Leadform extends Model
{
    protected $fillable = ['id','formrefid'];
	
	
	public function leadformsdatas(){
        return $this->hasMany(Leadformsdata::class);
    }
	
}
