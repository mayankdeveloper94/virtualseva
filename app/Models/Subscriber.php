<?php

namespace App\Models;

use Hyn\Tenancy\Traits\UsesSystemConnection;
use Illuminate\Database\Eloquent\Model;

class Subscriber extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['user_id','website_id','package_duration_id','expires','referrer','last_login','auto_renew','currency_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function website()
    {
        return $this->belongsTo(Website::class);
    }

    public function packageDuration(){
        return $this->belongsTo(PackageDuration::class);
    }

    public function currency(){
        return $this->belongsTo(Currency::class);
    }

}
