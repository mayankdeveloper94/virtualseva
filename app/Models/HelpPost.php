<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class HelpPost extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['title','content','sort_order','status'];

    public function helpCategories(){
        return $this->belongsToMany(HelpCategory::class);
    }
}
