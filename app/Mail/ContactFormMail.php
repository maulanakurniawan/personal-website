<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $name,
        public string $email,
        public string $subjectLine,
        public string $messageBody,
    ) {
    }

    public function build(): self
    {
        return $this->subject('[SoloHours] Contact form: ' . $this->subjectLine)
            ->replyTo($this->email, $this->name)
            ->view('emails.contact_form');
    }
}
