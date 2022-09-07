<?php

namespace App\Mail;

use App\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UserMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $email;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail= $this->from(setting('general_admin_email'),$this->email->user->name);
        $mail->subject($this->email->subject);
        $mail->replyTo($this->email->user->email);

        if($this->email->emailAttachments()->count()>0){

            foreach($this->email->emailAttachments as $attachement){
                $mail->attach($attachement->file_path);
            }

        }


        return $mail->view('mails.user_message');
    }
}
