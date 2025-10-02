<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomAdminMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectText;
    public $bodyMessage;

    public function __construct($subject, $message)
    {
        $this->subjectText = $subject;
        $this->bodyMessage = $message;
        $this->subject($subject);
    }

   public function build()
    {
        return $this->subject($this->subjectText)
                    ->view('emails.generic')   // <--- use your actual view name here
                    ->with([
                        'bodyMessage' => $this->bodyMessage,
                    ]);
    }

}
