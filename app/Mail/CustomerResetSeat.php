<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class CustomerResetSeat extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  public $passengerEmail;

  /**
   * Create a new message instance.
   */
  public function __construct(string $passengerEmail)
  {
    $this->passengerEmail = $passengerEmail;
    $this->onQueue('emails');
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    $mailFromAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS');

    return new Envelope(from: $mailFromAddress, subject: __('systemEmails.seat.reset.subject'));
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.customer-reset-seat',
      with: [
        'passengerEmail' => $this->passengerEmail,
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
