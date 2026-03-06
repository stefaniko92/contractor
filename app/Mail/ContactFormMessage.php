<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public string $senderName,
        public string $senderEmail,
        public string $messageSubject,
        public string $messageBody
    ) {}

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Kontakt forma: '.$this->messageSubject,
            replyTo: [$this->senderEmail],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-form-message',
            with: [
                'senderName' => $this->senderName,
                'senderEmail' => $this->senderEmail,
                'messageSubject' => $this->messageSubject,
                'messageBody' => $this->messageBody,
            ],
        );
    }
}
