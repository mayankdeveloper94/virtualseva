<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumThread extends Model
{
    protected $fillable = ['forum_topic_id','user_id','content'];

    public function forumTopic(){
        return $this->belongsTo(ForumTopic::class);
    }

    public function forumAttachments(){
        return $this->hasMany(ForumAttachment::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }
}
