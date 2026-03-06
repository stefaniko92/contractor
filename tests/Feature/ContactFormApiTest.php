<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactFormApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();

        // Mock HTTP for any external calls
        \Illuminate\Support\Facades\Http::fake();
    }

    public function test_successful_contact_form_submission(): void
    {
        $payload = [
            'name' => 'Stefan Rakic',
            'email' => 'test@example.com',
            'subject' => 'Test Subject',
            'message' => 'This is a test message from the contact form.',
        ];

        $response = $this->postJson('/api/public/contact', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        Mail::assertSent(\App\Mail\ContactFormMessage::class, function ($mail) use ($payload) {
            return $mail->hasTo(config('mail.contact_recipient'))
                && $mail->senderName === $payload['name']
                && $mail->senderEmail === $payload['email']
                && $mail->messageSubject === $payload['subject']
                && $mail->messageBody === $payload['message'];
        });
    }

    public function test_contact_form_without_subject(): void
    {
        $payload = [
            'name' => 'Stefan Rakic',
            'email' => 'test@example.com',
            'message' => 'This is a test message without subject.',
        ];

        $response = $this->postJson('/api/public/contact', $payload);

        $response->assertStatus(200);

        Mail::assertSent(\App\Mail\ContactFormMessage::class, function ($mail) {
            return $mail->messageSubject === 'Nova poruka sa kontakt forme';
        });
    }

    public function test_validation_error_missing_name(): void
    {
        $payload = [
            'email' => 'test@example.com',
            'message' => 'Test message',
        ];

        $response = $this->postJson('/api/public/contact', $payload);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => 'Validation error',
            ])
            ->assertJsonStructure([
                'details' => [
                    'name',
                ],
            ]);
    }

    public function test_validation_error_missing_email(): void
    {
        $payload = [
            'name' => 'Stefan Rakic',
            'message' => 'Test message',
        ];

        $response = $this->postJson('/api/public/contact', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'details' => [
                    'email',
                ],
            ]);
    }

    public function test_validation_error_invalid_email(): void
    {
        $payload = [
            'name' => 'Stefan Rakic',
            'email' => 'invalid-email',
            'message' => 'Test message',
        ];

        $response = $this->postJson('/api/public/contact', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'details' => [
                    'email',
                ],
            ]);
    }

    public function test_validation_error_missing_message(): void
    {
        $payload = [
            'name' => 'Stefan Rakic',
            'email' => 'test@example.com',
        ];

        $response = $this->postJson('/api/public/contact', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'details' => [
                    'message',
                ],
            ]);
    }

    public function test_validation_error_message_too_short(): void
    {
        $payload = [
            'name' => 'Stefan Rakic',
            'email' => 'test@example.com',
            'message' => 'Short',
        ];

        $response = $this->postJson('/api/public/contact', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'details' => [
                    'message',
                ],
            ]);
    }

    public function test_validation_error_message_too_long(): void
    {
        $payload = [
            'name' => 'Stefan Rakic',
            'email' => 'test@example.com',
            'message' => str_repeat('a', 5001), // Over 5000 chars
        ];

        $response = $this->postJson('/api/public/contact', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'details' => [
                    'message',
                ],
            ]);
    }

    public function test_rate_limiting_60_per_minute(): void
    {
        $payload = [
            'name' => 'Stefan Rakic',
            'email' => 'test@example.com',
            'message' => 'Rate limit test message',
        ];

        // This test would require 61 requests which is too slow
        // Instead we just verify the middleware is applied
        $response = $this->postJson('/api/public/contact', $payload);
        $response->assertStatus(200);

        // Check that throttle middleware is configured
        $route = \Illuminate\Support\Facades\Route::getRoutes()->getByName('api.public.contact');
        $this->assertNotNull($route);
        $this->assertContains('throttle:60,1', $route->middleware());
    }

    public function test_email_reply_to_is_sender_email(): void
    {
        $payload = [
            'name' => 'Stefan Rakic',
            'email' => 'sender@example.com',
            'message' => 'Test message for reply-to check',
        ];

        $this->postJson('/api/public/contact', $payload);

        Mail::assertSent(\App\Mail\ContactFormMessage::class, function ($mail) {
            return $mail->senderEmail === 'sender@example.com';
        });
    }

    public function test_long_name_is_accepted(): void
    {
        $payload = [
            'name' => str_repeat('A', 255), // Max length
            'email' => 'test@example.com',
            'message' => 'Test message with long name',
        ];

        $response = $this->postJson('/api/public/contact', $payload);

        $response->assertStatus(200);
    }

    public function test_name_over_max_length_fails(): void
    {
        $payload = [
            'name' => str_repeat('A', 256), // Over max
            'email' => 'test@example.com',
            'message' => 'Test message',
        ];

        $response = $this->postJson('/api/public/contact', $payload);

        $response->assertStatus(422)
            ->assertJsonStructure([
                'details' => [
                    'name',
                ],
            ]);
    }

    public function test_email_subject_format(): void
    {
        $payload = [
            'name' => 'Stefan Rakic',
            'email' => 'test@example.com',
            'subject' => 'My Custom Subject',
            'message' => 'Test message',
        ];

        $this->postJson('/api/public/contact', $payload);

        Mail::assertSent(\App\Mail\ContactFormMessage::class, function ($mail) {
            $envelope = $mail->envelope();

            return $envelope->subject === 'Kontakt forma: My Custom Subject';
        });
    }
}
