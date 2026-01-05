<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class CustomerResetPasswordSuccess extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    public $customer;
    public $locale;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, ?string $locale = null) {
        $this->customer = $user;
        $this->locale = $locale ?? config('app.locale');
        $this->locale($this->locale);
        $this->onQueue('emails');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope {
        $mailFromAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS');

        return new Envelope(from: $mailFromAddress, subject: __('systemEmails.password.confirmation.subject'));
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content {
        return new Content(
            view: 'emails.customer-reset-password-success',
            with: [
                'customerName' =>
                    $this->customer?->detail?->first_name ?? __('systemEmails.common.greeting.default_name'),
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
