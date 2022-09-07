<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class ArticleCategory extends Model
{
    use UsesSystemConnection;
    protected $fillable = ['name','sort_order'];

    public function articles(){
        return $this->belongsToMany(Article::class);
    }
}
