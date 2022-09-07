<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class OurWork extends Model
{
    use UsesSystemConnection;

    protected $fillable = ['title','description', 'icon'];

}
