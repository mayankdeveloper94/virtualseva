<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Advertisement extends Model
{
    protected $fillable= ['campid','title','description','image','website_url'];
    
    public static function booted()
    {
        static::creating(function ($ad) {
            $ad->ad_id = self::getNextAdID();
        });
    }
    
    public static function getNextAdID()
    {
        $adId = Str::random(20);
        
        if(Advertisement::where('ad_id',$adId)->exists()){
            self::getNextAdID();
        }
        
        return $adId;
    }
    
    public function departments(){
        return $this->belongsToMany(Department::class);
    }
    
    public function users(){
        return $this->belongsToMany(User::class);
    }
    
    public function analytics(){
        return $this->hasMany(Analytic::class);
    }
    
    public function campaign(){
        return $this->belongsTo(Campaign::class, 'campid');
    }
}
