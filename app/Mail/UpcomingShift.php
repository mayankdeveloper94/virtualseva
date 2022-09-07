<?php

namespace App\Mail;

use App\Shift;
use App\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpcomingShift extends Mailable
{
    use Queueable, SerializesModels;

    public $shift;
    public $user;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Shift $shift,User $user)
    {
        $this->shift = $shift;
        $this->user = $user;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $date = Carbon::parse($this->shift->event->event_date)->format('D d/M/Y');
        $subject = __('admin.shift-mail',['date'=>$date,'department'=>$this->shift->event->department->name]);
        return $this->view('mails.shift')->from(setting('general_admin_email'),setting('general_site_name'))->subject($subject);
    }
}
