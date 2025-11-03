<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class CustomerResetPassword extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  public $locale;
  public $customer;
  public $resetUrl;

  /**
   * Create a new message instance.
   */
  public function __construct(User $user, string $resetUrl, ?string $locale = null)
  {
    $this->customer = $user;
    $this->resetUrl = $resetUrl;
    $this->locale = $locale ?? config('app.locale');
    $this->locale($this->locale);
    $this->onQueue('emails');
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    $mailFromAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS');

    return new Envelope(from: $mailFromAddress, subject: __('systemEmails.reset_password_request_subject'));
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.customer-reset-password',
      with: [
        'customer' => $this->customer,
        'resetUrl' => $this->resetUrl,
      ]
    );
  }

  /**
   * Get the attachments for the message.
   *
   * @return array<int, \Illuminate\Mail\Mailables\Attachment>
   */
  public function attachments(): array
  {
    return [];
  }
}
