<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ForumAttachment extends Model
{
    protected $fillable = ['forum_thread_id','file_path'];

    public function forumThread(){
        return $this->belongsTo(ForumThread::class);
    }

}
