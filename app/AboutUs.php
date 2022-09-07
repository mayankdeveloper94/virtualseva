<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AboutUs extends Model
{
    protected $fillable= ['content'];
    
    protected $table = 'about_us';
    
}
