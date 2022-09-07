<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Analytic extends Model
{
    protected $fillable= ['user_id','advertisement_id','no_of_clicks'];
    
    public function reports()
    {
        return $this->hasMany(AnalyticReport::class);
    }
    
}
