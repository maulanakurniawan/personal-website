<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Throwable;

class TestEmailSendingCommand extends Command
{
    protected $signature = 'mail:test-send
        {to : Email address that should receive the test message}
        {--subject=SoloHours mail send test : Email subject line}
        {--body=This is a test email from SoloHours. : Email body content}';

    protected $description = 'Send a test email to verify outbound mail delivery settings';

    public function handle(): int
    {
        $to = (string) $this->argument('to');

        if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
            $this->error("Invalid recipient email: {$to}");

            return self::FAILURE;
        }

        try {
            Mail::raw((string) $this->option('body'), function ($message) use ($to): void {
                $message
                    ->to($to)
                    ->subject((string) $this->option('subject'));
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
