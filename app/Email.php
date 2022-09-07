<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Email extends Model
{

    protected $fillable = ['subject','message','notes','user_id'];

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function users(){
        return $this->belongsToMany(User::class)->withPivot('read');
    }

    public function emailAttachments(){
        return $this->hasMany(EmailAttachment::class);
    }

    public function departments(){
        return $this->belongsToMany(Department::class);
    }
}
