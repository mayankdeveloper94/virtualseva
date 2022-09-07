<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Hyn\Tenancy\Traits\UsesSystemConnection;

class BlogPost extends Model
{
    use UsesSystemConnection;
    protected $fillable =['title','content','published_on','status','cover_image','meta_title','meta_description','user_id'];

    public function blogCategories(){
        return $this->belongsToMany(BlogCategory::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }


}
