<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AnalyticReport extends Model
{
    protected $fillable= ['analytic_id','date','clicks'];
    
}
