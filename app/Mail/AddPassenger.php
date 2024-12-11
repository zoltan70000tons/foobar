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

class AddPassenger extends Mailable
{
  use Queueable, SerializesModels;

  public $getSignedURL;
  public $survivorNumber;

  /**
   * Create a new message instance.
   */
  public function __construct($getSignedURL, $survivorNumber)
  {
    $this->getSignedURL = $getSignedURL;
    $this->survivorNumber = $survivorNumber;
    // Generate the activation (verification) link
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      from: "smtp@bspmi.com",
      subject: "70000TONS OF METAL - SOMEBODY INVITE YOU TO 70000TONS OF METAL!"
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: "emails.addpax",
      with: [
        "survivorNumber" => $this->survivorNumber,
        "getSignedURL" => $this->getSignedURL,
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
