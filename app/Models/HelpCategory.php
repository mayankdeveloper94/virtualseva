<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class HelpCategory extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['name','sort_order'];

    public function helpPosts(){
        return $this->belongsToMany(HelpPost::class);
    }

}
