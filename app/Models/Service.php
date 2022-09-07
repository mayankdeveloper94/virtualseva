<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class Service extends Model
{
    use UsesSystemConnection;

    protected $fillable = ['title','description', 'icon'];

}
