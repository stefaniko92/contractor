<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WelcomeNewUser extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public User $user,
        public string $resetToken
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Dobrodošli na Pausalci.com - Postavite šifru',
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $resetUrl = url(config('app.url').route('password.reset', [
            'token' => $this->resetToken,
            'email' => $this->user->email,
        ], false));

        return new Content(
            view: 'emails.welcome-new-user',
            with: [
                'user' => $this->user,
                'resetUrl' => $resetUrl,
            ],
        );
    }
}
