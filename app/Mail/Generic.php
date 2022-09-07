<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Generic extends Mailable
{
    use Queueable, SerializesModels;


    public $subject;
    public $msg;
    public $sender;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subject,$message,$from=null)
    {
        if(empty($from)){
            $from =['address'=>setting('general_admin_email'),'name'=>setting('general_site_name')];

        }

        $this->subject = $subject;
        $this->msg = $message;
        $this->sender = $from;


    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from($this->sender['address'],$this->sender['name'])->subject($this->subject)->view('mails.generic');
    }
}
