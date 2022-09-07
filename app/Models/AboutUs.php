<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class AboutUs extends Model
{
    use UsesSystemConnection;

    protected $fillable = ['description'];

}
