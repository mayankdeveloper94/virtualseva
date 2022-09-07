<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password','role_id','telephone','gender','picture','about','status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];


    public function teams(){
        return $this->belongsToMany(Team::class);
    }

    public function departments(){
        return $this->belongsToMany(Department::class)->withPivot('department_admin');
    }

    public function departmentFields(){
        return $this->belongsToMany(DepartmentField::class)->withPivot('value');
    }

    public function applications(){
        return $this->hasMany(Application::class);
    }

    public function fields(){
        return $this->belongsToMany(Field::class)->withPivot('value');
    }


    public function emails(){
        return $this->hasMany(Email::class);
    }

    public function receivedEmails(){
        return $this->belongsToMany(Email::class)->withPivot('read');
    }

    public function sms(){
        return $this->hasMany(Sms::class);
    }

    public function receivedSms(){
        return $this->belongsToMany(Sms::class);
    }

    public function shifts(){
        return $this->belongsToMany(Shift::class)->withPivot('tasks');
    }

    public function rejections(){
        return $this->hasMany(Rejection::class);
    }

    public function downloads(){
        return $this->hasMany(Download::class);
    }
    
    public function advertisements(){
        return $this->belongsToMany(Advertisement::class);
    }
}
