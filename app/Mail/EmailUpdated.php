<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class EmailUpdated extends Mailable
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
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      from: 'smtp@bspmi.com',
      subject: __('systemEmails.update_email.subject', [], $this->language),
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
