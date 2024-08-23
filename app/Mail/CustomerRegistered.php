<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Customer;

class CustomerRegistered extends Mailable
{
  use Queueable, SerializesModels;

  public $customer;
  public $language;

  /**
   * Create a new message instance.
   */
  public function __construct(Customer $customer, String $language)
  {
    $this->customer = $customer;
    $this->language = $language;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    return new Envelope(
      from: 'smtp@bspmi.com',
      subject: 'Customer Registered',
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.customer-registered',
      with: [
        'customer' => $this->customer,
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
