<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class EmailUpdated extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  public $user;
  public $language;

  /**
   * Create a new message instance.
   */
  public function __construct(User $user, string $language)
  {
    $this->user = $user;
    $this->language = $language;
    $this->onQueue('emails');
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    $mailFromAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS');

    return new Envelope(
      from: $mailFromAddress,
      subject: __('systemEmails.account.update.subject', [], $this->language)
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.email-updated',
      with: [
        'user' => $this->user,
        'language' => $this->language,
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
