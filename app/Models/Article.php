<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class Article extends Model
{
    use UsesSystemConnection;

    protected $fillable = ['menu_title','page_title','content','sort_order','meta_title','meta_description'];


    public function articleCategories(){
        return $this->belongsToMany(ArticleCategory::class);
    }


}
