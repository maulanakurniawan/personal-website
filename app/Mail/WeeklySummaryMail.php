<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Collection;

class WeeklySummaryMail extends Mailable
{
    use Queueable;

    public function __construct(
        public string $userName,
        public int $totalSeconds,
        public int $billableSeconds,
        public ?float $estimatedRevenue,
        public Collection $topProjects,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your SoloHours Weekly Summary');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-summary',
        );
    }
}
