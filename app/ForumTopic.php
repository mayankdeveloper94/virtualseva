<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumTopic extends Model
{
    protected $fillable = ['user_id','department_id','topic'];

    public function forumThreads(){
        return $this->hasMany(ForumThread::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

    public function forumAttachments(){
        return $this->hasManyThrough(ForumAttachment::class,ForumThread::class);
    }

}
