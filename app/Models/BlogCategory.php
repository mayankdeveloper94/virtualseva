<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class BlogCategory extends Model
{
    use UsesSystemConnection;
    protected $fillable= ['category','sort_order'];

    public function blogPosts(){
        return $this->belongsToMany(BlogPost::class);
    }
}
