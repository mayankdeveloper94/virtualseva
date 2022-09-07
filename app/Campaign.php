<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Campaign extends Model{
    protected $fillable= ['title','description','image','website_url'];
    
    public function Advertisement(){
        return $this->hasMany(Advertisement::class, 'campid');
    }
    
}