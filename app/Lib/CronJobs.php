<?php
namespace App\Lib;

use App\Event;
use App\Mail\UpcomingShift;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class CronJobs
{

    public function deleteTempFiles(){
        $path = '../storage/tmp';

        $objects = scandir($path);
        foreach ($objects as $object) {
             $dir = $path.'/'.$object;
            if(is_dir($dir) && $object !='..' && $object !='.'){
                if (filemtime($dir) < time() - 86400) {
                    deleteDir($dir);
                }
            }

        }
    }

    public function upcomingEvents(){

        $events = Event::where('event_date','>',Carbon::now()->toDateTimeString())->where('event_date' , '<=' , Carbon::now()->addDays(3)->toDateTimeString())->get();

        foreach($events as $event){
            foreach($event->shifts as $shift){
                //get users for this shift
                foreach($shift->users as $user){
                    try{
                        Mail::to($user->email)->send(New UpcomingShift($shift,$user));
                        echo 'Mail sent to '.$user->name.'<br/>';
                    }
                    catch(\Exception $ex){

                    }
                }
            }
        }

    }

}