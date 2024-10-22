<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class CustomerResetPassword extends Mailable
{
  use Queueable, SerializesModels;

  public $customer;
  public $resetUrl;

  /**
   * Create a new message instance.
   */
  public function __construct(User $user, string $resetUrl)
  {
    $this->customer = $user;
    $this->resetUrl = $resetUrl;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      from: 'smtp@bspmi.com',
      subject: 'Customer Reset Password',
    );
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
      ],
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
