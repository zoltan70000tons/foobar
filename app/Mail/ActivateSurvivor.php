<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class ActivateSurvivor extends Mailable
{
  use Queueable, SerializesModels;

  public $customer;
  public $language;
  public $survivorNumber;
  public $activationLink;

  /**
   * Create a new message instance.
   */
  public function __construct(User $customer, string $language, string $survivorNumber)
  {
    $this->customer = $customer;
    $this->language = $language;
    $this->survivorNumber = $survivorNumber;

    // Generate the activation (verification) link
    $this->activationLink = URL::temporarySignedRoute('verificationApi.verify', now()->addMinutes(60), [
      'id' => $customer->id,
      'hash' => sha1($customer->email),
    ]);
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    // use MAIL_FROM_ADDRESS in .env
    $mailFromAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS');

    return new Envelope(from: $mailFromAddress, subject: '70000TONS OF METAL - SURVIVOR YOUR ACCOUNT IS ACTIVE!');
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
        'activationLink' => $this->activationLink,
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
