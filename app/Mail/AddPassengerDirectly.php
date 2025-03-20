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

class AddPassengerDirectly extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  public $bookingCode;
  public $url;
  public $fromWho;
  public $toWho;
  public $event;

  /**
   * Create a new message instance.
   */
  public function __construct($bookingCode, $fromWho, $toWho, $event)
  {
    $this->url = config('app.frontend_url') . '/en/login';

    $this->bookingCode = $bookingCode;

    $this->fromWho = $fromWho;
    $this->toWho = $toWho;
    $this->event = $event;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    $mailFromAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS');

    return new Envelope(from: $mailFromAddress, subject: "{$this->fromWho} - invites you to join their cabin!");
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
        'fromWho' => $this->fromWho,
        'toWho' => $this->toWho,
        'event_name' => $this->event->name,
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
