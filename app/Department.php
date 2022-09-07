<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
  //  protected $guarded = ['id'];
    protected $fillable = ['name','description','enroll_open','approval_required','picture','show_members','allow_members_communicate','enable_forum','allow_members_create_topics','enable_roster','enable_announcements','enable_resources','allow_members_upload','enable_blog','allow_members_post','visible','enabled','enable_sms'];

    public function departmentFields(){
        return $this->hasMany(DepartmentField::class);
    }

    public function users(){
        return $this->belongsToMany(User::class)->withPivot('department_admin');
    }

    public function teams(){
        return $this->hasMany(Team::class);
    }

    public function categories(){
        return $this->belongsToMany(Category::class);
    }

    public function emails(){
        return $this->belongsToMany(Email::class);
    }

    public function applications(){
        return $this->hasMany(Application::class);
    }

    public function events(){
        return $this->hasMany(Event::class);
    }

    public function announcements(){
        return $this->hasMany(Announcement::class);
    }

    public function galleries(){
        return $this->hasMany(Gallery::class);
    }

    public function downloads(){
        return $this->hasMany(Download::class);
    }

    public function forumTopics(){
        return $this->hasMany(ForumTopic::class);
    }
    
    public function advertisements(){
        return $this->belongsToMany(Advertisement::class);
    }


}
