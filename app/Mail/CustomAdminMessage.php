<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomAdminMessage extends Mailable
{
    use Queueable, SerializesModels;

    public $subjectLine;
    public $bodyText;

    public function __construct($subjectLine, $bodyText)
    {
        $this->subjectLine = $subjectLine;
        $this->bodyText = $bodyText;
    }

    public function build()
    {
        return $this->subject($this->subjectLine)
                    ->view('emails.custom_admin_message')
                    ->with(['bodyText' => $this->bodyText]);
    }
}
