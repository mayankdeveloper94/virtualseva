<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class Slider extends Model
{
    use UsesSystemConnection;

    protected $fillable = ['title','description','image'];

}
