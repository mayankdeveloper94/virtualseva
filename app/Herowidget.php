<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Herowidget extends Model
{
    protected $fillable = ['id','alignment','title','upload_image','description1','description2','button_status','button_text','button_resourcelink','image_videoembed','embed_script'];
	
	
}
