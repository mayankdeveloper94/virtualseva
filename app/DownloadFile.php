<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class DownloadFile extends Model
{
    protected $fillable = ['download_id','file_path'];

    public function download(){
        return $this->belongsTo(Download::class);
    }
}
