<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;

class AddPassengerDirectly extends Mailable
{
  use Queueable, SerializesModels;

  public $bookingCode;
  public $url;

  /**
   * Create a new message instance.
   */
  public function __construct($bookingCode)
  {
    $this->url = config('app.frontend_url' . '/en/login');

    $this->bookingCode = $bookingCode;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    $mailFromAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS');

    return new Envelope(
      from: $mailFromAddress,
      subject: '70000TONS OF METAL - SOMEBODY INVITE YOU TO 70000TONS OF METAL!'
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.addpax-directly',
      with: [
        'bookingCode' => $this->bookingCode,
        'url' => $this->url,
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
