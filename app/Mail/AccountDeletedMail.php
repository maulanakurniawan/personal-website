<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class AccountDeletedMail extends Mailable
{
    public function __construct(private readonly string $name)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your SoloHours account has been deleted',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account_deleted',
            with: [
                'name' => $this->name,
            ],
        );
    }
}
