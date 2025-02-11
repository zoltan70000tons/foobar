<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CustomerConfirmationBooking extends Mailable
{
  use Queueable, SerializesModels;

  public $booking;
  public $cabinType;
  public $language;

  public function __construct($booking, string $cabinType, string $language)
  {
    $this->booking = $booking;
    $this->cabinType = $cabinType;
    $this->language = $language;
  }

  private function prepareDataForTemplate()
  {
    \Log::info('Booking ----> data: ' . json_encode($this->booking));

    $passenger =
      collect($this->booking->passengers)->firstWhere('lead_passenger', true) ??
      collect($this->booking->passengers)->first();

    return (object) [
      'passenger' => (object) [
        'first_name' => $passenger->first_name ?? 'N/A',
        'middle_name' => $passenger->middle_name ?? 'N/A',
        'last_name' => $passenger->last_name ?? 'N/A',
        'email' => $passenger->email ?? 'N/A',
        'gender' => $passenger->gender ?? 'N/A',
        'date_of_birth' => $passenger->dob ?? 'N/A',
        'citizenship' => $passenger->citizenship ?? 'N/A',
        'address_line_1' => $passenger->address_first ?? 'N/A',
        'address_line_2' => $passenger->address_second ?? 'N/A',
        'city' => $passenger->city ?? 'N/A',
        'state' => $passenger->state ?? 'N/A',
        'postal_code' => $passenger->postal_code ?? 'N/A',
        'country' => $passenger->country ?? 'N/A',
        'phone_number' => $passenger->phone ?? 'N/A',
        'emergency_contact_name' => $passenger->emergency_c_name ?? 'N/A',
        'emergency_phone_number' => $passenger->emergency_c_phone ?? 'N/A',
        'special_request' => $passenger->special_request ?? 'N/A',
        'survivor_referal_number' => $passenger->referral_details ?? 'N/A',
        'how_did_you_hear_about_us' => $passenger->hear_about ?? 'N/A',
        'receive_newsletter' => $passenger->newsletter ?? 'N/A',
        'receive_partner_information' => $passenger->travel_info ?? 'N/A',
        'accept_bed_configuration' => $passenger->cabin_conf_accp ?? 'N/A',
        'accept_terms' => $passenger->terms_n_cons ?? 'N/A',
      ],
      'booking' => (object) [
        'booking_type' => $this->cabinType ?? 'N/A',
        'cabin_category' => $this->booking->cabin->category->category_name ?? 'N/A',
        'form_of_payment' => $this->booking->payment_plan ?? 'N/A',
        'official_ticket_price_per_person' => number_format($this->booking->cabin->category->price ?? 0, 2),
        'pay_in_full_discount' => $this->booking->cabin->category->discount ?? 0,
        'net_ticket_price_per_person' => $this->calculateNetTicketPrice(
          $passenger->passenger_allocated_cost ?? 0,
          $this->booking->adjustments ?? []
        ),
        'taxes_and_fees_per_person' => $this->getTaxAdjustment($this->booking->adjustments ?? []),
        'single_traveler_surcharge' => $this->getSingleTicketFee($this->booking->adjustments ?? []),
        'total_ticket_price' => $passenger->passenger_allocated_cost ?? 0,
        'number_of_passengers' => $this->booking->cabin->capacity ?? 1,
        'grand_total_booking_price' => $this->calculateTotalTicketPrice(
          $passenger->passenger_allocated_cost ?? 0,
          $this->booking->cabin->capacity ?? 1,
          $this->cabinType
        ),
        'payment_schedule' => $this->booking->payment_plan ?? 'N/A',
        'todays_date' => now()->format('Y-m-d'),
        'booking_request_id' => $this->booking->booking_request_id ?? 'N/A',
      ],
    ];
  }

  private function calculateNetTicketPrice($price, $adjustments)
  {
    $percentageDiscount = collect($adjustments)
      ->where('type', 'DISCOUNT')
      ->where('operation', 'PERCENTAGE')
      ->sum('value');

    $fixedDiscount = collect($adjustments)->where('type', 'DISCOUNT')->where('operation', 'FIXED')->sum('value');

    $totalDiscount = ($price * $percentageDiscount) / 100 + $fixedDiscount;

    return max(0, $price - $totalDiscount);
  }

  private function calculateTotalTicketPrice($price, $capacity, $cabinType)
  {
    return $cabinType === 'Private Cabin' ? $price * $capacity : $price;
  }

  private function getTaxAdjustment($adjustments)
  {
    return collect($adjustments)->firstWhere('code', 'TAX')->value ?? 0;
  }

  private function getSingleTicketFee($adjustments)
  {
    return collect($adjustments)->firstWhere('code', 'SINGLE_TICKET_FEE')->value ?? 0;
  }

  public function envelope(): Envelope
  {
    $data = $this->prepareDataForTemplate();

    return new Envelope(
      from: env('SMTP_SYSTEM_EMAIL_ADDRESS', 'smtp@bspmi.com'),
      subject: "{$data->passenger->first_name} - your Booking Request for 70000TONS OF METAL 2025!"
    );
  }

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

  public function attachments(): array
  {
    return [];
  }
}
