<?php

namespace App\Console\Commands;

use App\Mail\ContactFormMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmailCommand extends Command
{
    protected $signature = 'mail:test
        {--to= : Recipient email address (defaults to MAIL_SUPPORT_ADDRESS)}
        {--name=Test Sender : Name shown in reply-to}
        {--from=test@example.com : Email shown in reply-to}
        {--subject=Test Email : Subject for the test message}
        {--message=This is a test email sent from the artisan command. : Message body for the test message}';

    protected $description = 'Send a test contact-form email to verify mail configuration';

    public function handle(): int
    {
        $recipient = $this->option('to') ?: config('mail.support.address');

        if (empty($recipient)) {
            $this->error('No recipient email configured. Provide --to or set MAIL_SUPPORT_ADDRESS.');

            return self::FAILURE;
        }

        $mailable = new ContactFormMail(
            name: (string) $this->option('name'),
            email: (string) $this->option('from'),
            subjectLine: (string) $this->option('subject'),
            messageBody: (string) $this->option('message'),
        );

        Mail::to($recipient)->send($mailable);

        $this->info("Test email sent to {$recipient}.");

        return self::SUCCESS;
    }
}
