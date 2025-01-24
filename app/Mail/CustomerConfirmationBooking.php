<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\User;

class CustomerConfirmationBooking extends Mailable
{
  use Queueable, SerializesModels;

  public $bookingResult;
  public $language;

  /**
   * Create a new message instance.
   */
  public function __construct($bookingResult, string $language)
  {
    $this->bookingResult = $bookingResult;
    $this->language = $language;
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    $passenger = $this->bookingResult['passenger']['App\\Models\\Passenger'] ?? null;

    if (!$passenger) {
      throw new \Exception('Passenger data is missing from the booking result.');
    }

    $name = $passenger['first_name'] . ' ' . $passenger['last_name'];

    return new Envelope(
      from: 'smtp@bspmi.com',
      subject: $name . ' - your Booking Request for 70000TONS OF METAL 2025!'
    );
  }

  /**
   * Get the message content definition.
   */
  public function content(): Content
  {
    return new Content(
      view: 'emails.customer-confirmation-booking',
      with: [
        'bookingResult' => $this->bookingResult,
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
