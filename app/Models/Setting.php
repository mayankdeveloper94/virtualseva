<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class Setting extends Model
{
    use UsesSystemConnection;
    protected $fillable =['key','label','placeholder','value','serialized','type','options','class','sort_order'];
}
