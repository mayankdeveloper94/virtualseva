<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class FAQ extends Model
{
    use UsesSystemConnection;
    
    protected $table = 'faqs';

    protected $fillable = ['question','answer'];

}
