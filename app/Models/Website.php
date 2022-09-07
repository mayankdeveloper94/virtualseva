<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Models\Website as HynWebsite;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class Website extends HynWebsite
{
    use UsesSystemConnection;
    public function subscriber(){
        return $this->hasOne(Subscriber::class);
    }


}
