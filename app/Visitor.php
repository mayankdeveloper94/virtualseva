<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Visitor extends Model{
    protected $fillable= ['user_id','advertisement_id','ip_address'];
    
}