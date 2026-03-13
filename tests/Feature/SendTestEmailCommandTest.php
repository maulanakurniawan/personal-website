<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendTestEmailCommandTest extends TestCase
{
    public function test_mail_test_command_sends_contact_form_mail(): void
    {
        Mail::fake();

        $this->artisan('mail:test', [
            '--to' => 'recipient@example.com',
            '--name' => 'Command Sender',
            '--from' => 'sender@example.com',
            '--subject' => 'Command Subject',
            '--message' => 'Command body',
        ])->assertSuccessful();

        Mail::assertSent(ContactFormMail::class, function (ContactFormMail $mail) {
            return $mail->hasTo('recipient@example.com')
                && $mail->email === 'sender@example.com'
                && $mail->name === 'Command Sender'
                && $mail->subjectLine === 'Command Subject'
                && $mail->messageBody === 'Command body';
        });
    }
}
