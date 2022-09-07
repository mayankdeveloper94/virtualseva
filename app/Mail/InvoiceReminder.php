<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class InvoiceReminder extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $expires;
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $expires = $this->invoice->user->subscriber->expires;
        $this->expires = $expires;
        $days = ($expires - time())/86400;
        $days = floor($days);

        if($days > 0 ){
            $subject =  __('saas.subscription-expires',['days'=>$days]);
        }
        else{
            $subject = __('saas.subscription-a-expire');
        }

        if($expires < time()){
            $subject = __('saas.subscription-has-expired');
        }

        

        return $this->subject($subject)->view('mails.expiration');
    }
}
