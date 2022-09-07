<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hostname extends \Hyn\Tenancy\Models\Hostname
{
    protected $fillable = ['fqdn','redirect_to','force_https','website_id'];
}
