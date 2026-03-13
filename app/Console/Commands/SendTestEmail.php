<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendTestEmail extends Command
{
    protected $signature = 'mail:test
        {to : Email address that should receive the test message}
        {--subject=SoloHours test email : Email subject line}
        {--body=This is a test email from SoloHours. : Email body content}';

    protected $description = 'Send a test email using the currently configured mailer';

    public function handle(): int
    {
        $to = (string) $this->argument('to');

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid recipient email: {$to}");

            return self::FAILURE;
        }

        $subject = (string) $this->option('subject');
        $body = (string) $this->option('body');

        try {
            Mail::raw($body, function ($message) use ($to, $subject): void {
                $message->to($to)->subject($subject);
            });
        } catch (Throwable $exception) {
            $this->error('Failed to send test email.');
            $this->line($exception->getMessage());

            return self::FAILURE;
        }

        $this->info("Test email sent to {$to} using [" . config('mail.default') . '] mailer.');

        return self::SUCCESS;
    }
}
