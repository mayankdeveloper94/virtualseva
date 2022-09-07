<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class Country extends Model
{
    use UsesSystemConnection;
    protected $fillable =['name','iso_code_2','iso_code_3','address_format','postcode_required','status','currency_name','currency_code','symbol_left','symbol_right'];



}
