<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Download extends Model
{
    protected $fillable =  ['user_id','department_id','name','description'];

    public function downloadFiles(){
        return $this->hasMany(DownloadFile::class);
    }

    public function department(){
        return $this->belongsTo(Department::class);
    }

    public function user(){
        return $this->belongsTo(User::class);
    }

}
