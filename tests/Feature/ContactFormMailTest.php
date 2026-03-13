<?php

namespace Tests\Feature;

use App\Mail\ContactFormMail;
use Tests\TestCase;

class ContactFormMailTest extends TestCase
{
    public function test_contact_form_mail_builds_expected_content(): void
    {
        $mailable = new ContactFormMail(
            name: 'Maulana',
            email: 'sender@example.com',
            subjectLine: 'Project inquiry',
            messageBody: 'Need help with Laravel architecture.',
        );

        $mailable->assertHasSubject('[Maulana Kurniawan] Contact form: Project inquiry');
        $mailable->assertHasReplyTo('sender@example.com', 'Maulana');
        $mailable->assertSeeInHtml('Need help with Laravel architecture.');
        $mailable->assertSeeInText('Need help with Laravel architecture.');
    }
}
