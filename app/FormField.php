<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $fillable = ['id','field_labelname','field_placeholder','field_type','field_sortorder','field_required','field_enabled'];
	
	public function formoptions(){
        return $this->hasMany(FormOption::class);
    }
}
