<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormOption extends Model
{
    // protected $guarded = ['id','form_field_id','formoptions_options','formoptions_value'];
    protected $fillable = ['id','form_field_id','formoptions_options','formoptions_value'];
	
	public function formfield(){
        return $this->belongsTo(FormField::class);
    }
}
