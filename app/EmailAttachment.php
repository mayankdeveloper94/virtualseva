<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmailAttachment extends Model
{
    protected $fillable = ['email_id','file_name','file_path'];

    public function email(){
        return $this->belongsTo(Email::class);
    }
}
