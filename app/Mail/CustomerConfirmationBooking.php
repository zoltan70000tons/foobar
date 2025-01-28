<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerConfirmationBooking extends Mailable
{
  use Queueable, SerializesModels;

  public $booking;
  public $language;

  /**
   * Create a new message instance.
   */
  public function __construct($booking, string $language)
  {
    $this->booking = $booking;
    $this->language = $language;
  }

  private function prepareDataForTemplate(): array
  {
    return [
      'passenger' => [
        'first_name' => $this->booking->passenger->first_name ?? 'N/A',
        'last_name' => $this->booking->passenger->last_name ?? 'N/A',
        'email' => $this->booking->passenger->email ?? 'N/A',
        'gender' => $this->booking->passenger->gender ?? 'N/A',
        'date_of_birth' => $this->booking->passenger->dob ?? 'N/A',
        'citizenship' => $this->booking->passenger->citizenship ?? 'N/A',
        'address_line_1' => $this->booking->passenger->address_first ?? 'N/A',
        'address_line_2' => $this->booking->passenger->address_second ?? 'N/A',
        'city' => $this->booking->passenger->city ?? 'N/A',
        'state' => $this->booking->passenger->state ?? 'N/A',
        'postal_code' => $this->booking->passenger->postal_code ?? 'N/A',
        'country' => $this->booking->passenger->country ?? 'N/A',
        'phone_number' => $this->booking->passenger->phone ?? 'N/A',
        'emergency_contact_name' => $this->booking->passenger->emergency_c_name ?? 'N/A',
        'emergency_phone_number' => $this->booking->passenger->emergency_c_phone ?? 'N/A',
        'special_request' => $this->booking->passenger->special_request ?? 'N/A',
        'survivor_referal_number' => $this->booking->passenger->referral_details ?? 'N/A',
        'how_did_you_hear_about_us' => $this->booking->passenger->hear_about ?? 'N/A',
        'receive_newsletter' => $this->booking->passenger->newsletter ?? 'N/A',
        'receive_partner_information' => $this->booking->passenger->travel_info ?? 'N/A',
        'accept_bed_configuration' => $this->booking->passenger->cabin_conf_accp ?? 'N/A',
        'accept_terms' => $this->booking->passenger->terms_n_cons ?? 'N/A',
      ],
      'booking' => [
        'booking_type' => $this->booking->cabin->cabin_type->cabin_type ?? 'N/A',
        'cabin_category' => $this->booking->cabin->category->category_name ?? 'N/A',
        'form_of_payment' => $this->booking->payment_plan ?? 'N/A',
        'official_ticket_price_per_person' => number_format($this->booking->cabin->category->price, 2) ?? 'N/A',
        'pay_in_full_discount' => $this->booking->cabin->category->discount ?? 'N/A',
        'net_ticket_price_per_person' => 'N/A',
        'taxes_and_fees_per_person' => 'N/A',
        'single_traveler_surcharge' => 'N/A',
        'total_ticket_price' => 'N/A',
        'number_of_passengers' => $this->booking->cabin->capacity ?? 'N/A',
        'grand_total_booking_price' => 'N/A',
        'payment_schedule' => $this->booking->payment_plan ?? 'N/A',
        'todays_date' => now()->format('Y-m-d'),
        'booking_request_id' => $this->booking->booking_request_id ?? 'N/A',
      ],
    ];
  }

  /**
   * Get the message envelope.
   */
  public function envelope(): Envelope
  {
    $data = $this->prepareDataForTemplate();
    // from - use env variable
    $systemEmailAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS', 'smtp@bspmi.com');

    return new Envelope(
      from: $systemEmailAddress,
      subject: $data['passenger']['first_name'] . ' - your Booking Request for 70000TONS OF METAL 2025!'
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
        'bookingResult' => $this->prepareDataForTemplate(),
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
