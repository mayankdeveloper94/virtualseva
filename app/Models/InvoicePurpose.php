<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class InvoicePurpose extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['purpose','code'];

    public function invoices(){
        return $this->hasMany(Invoice::class);
    }
}
