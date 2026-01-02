<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerVerificationEmail extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public $user;
    public $verificationUrl;
    public $language;
    public $locale;

    public function __construct($user, $verificationUrl, ?string $language = null) {
        $this->user = $user;
        $this->verificationUrl = $verificationUrl;
        $this->language = $language;
        $this->locale = $language ?? config('app.locale');
        $this->locale($this->locale);
        $this->onQueue('emails');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        $mailFromAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS');

        return new Envelope(from: $mailFromAddress, subject: __('systemEmails.account.verification.title'));
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.customer-verification',
            with: [
                'user' => $this->user,
                'verificationUrl' => $this->verificationUrl,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array {
        return [];
    }
}
