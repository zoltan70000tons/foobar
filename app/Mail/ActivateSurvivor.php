<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class ActivateSurvivor extends Mailable
{
  use Queueable, SerializesModels;

  public $customer;
  public $language;
  public $survivorNumber;

  /**
   * Create a new message instance.
   */
  public function __construct(User $customer, string $language, string $survivorNumber)
  {
    $this->customer = $customer;
    $this->language = $language;
    $this->survivorNumber = $survivorNumber;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(from: 'smtp@bspmi.com', subject: '70000TONS OF METAL - SURVIVOR YOUR ACCOUNT IS ACTIVE!');
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.activate-survivor',
      with: [
        'customer' => $this->customer,
        'language' => $this->language,
        'survivorNumber' => $this->survivorNumber,
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
