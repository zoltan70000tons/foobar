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
  public $cart;
  public $installments;

  public function __construct($booking, string $cabinType, array $installments, array $cart, string $language)
  {
    $this->booking = $booking;
    $this->cabinType = $cabinType;
    $this->language = $language;
    $this->cart = $cart;
    $this->installments = $installments;
  }

  // PREPARE DATA FOR TEMPLATE
  private function prepareDataForTemplate()
  {
    \Log::info('Booking ----> data: ' . json_encode($this->booking));
    \Log::info('Cart ----> data: ', ['cart' => $this->cart]);

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
        'accept_bed_configuration' => $passenger->cabin_conf_accp ? 'YES' : 'N/A',
        'accept_terms' => $passenger->terms_n_cons ? 'YES' : 'N/A',
      ],
      'booking' => (object) [
        'booking_type' => $this->cabinType ?? 'N/A',
        'cabin_category' => $this->booking->cabin->category->category_name ?? 'N/A',
        'form_of_payment' => $this->booking->payment_plan ?? 'N/A',
        'official_ticket_price_per_person' => number_format($this->cart['cabin_price'] ?? 0, 2),
        'pay_in_full_discount' => $this->booking->cabin->category->discount ?? 0,
        'net_ticket_price_per_person' => $this->calculateNetTicketPrice(
          $this->cart['cabin_price'],
          $this->cart['price_save'],
          $this->cart['cabin_capacity']
        ),
        'taxes_and_fees_per_person' => $this->getTaxAdjustment(
          $this->cart['price_extras'],
          $this->cabinType !== 'Private Cabin' ? 1 : $this->cart['cabin_capacity']
        ),
        'single_traveler_surcharge' => $this->cabinType !== 'Private Cabin' ? '100' : 'N/A',
        'total_ticket_price' => $passenger->passenger_allocated_cost ?? 0,
        'number_of_passengers' => $this->cart['cabin_capacity'] ?? 1,
        'grand_total_booking_price' => $this->cart['price_total'] ?? 'N/A',
        'payment_schedule' => !empty($this->installments)
          ? collect($this->installments)
            ->map(
              fn($installment) => [
                'due_date' => $installment['due_date'],
                'amount' => number_format($installment['amount'], 2),
              ]
            )
            ->toArray()
          : 'N/A',
        'todays_date' => now()->format('Y-m-d'),
        'booking_request_id' => $this->booking->booking_request_id ?? 'N/A',
      ],
    ];
  }

  // CALCULATE NET TICKET PRICE
  private function calculateNetTicketPrice($cabinPrice, $save, $capacity)
  {
    $totalSave = $save * $capacity;
    $netPrice = $cabinPrice - $totalSave;

    return number_format($netPrice, 2);
  }

  // GET TAX ADJUSTMENT
  private function getTaxAdjustment($extras, $capacity)
  {
    $addons = $extras * $capacity;

    return number_format($addons, 2);
  }

  public function envelope(): Envelope
  {
    $data = $this->prepareDataForTemplate();

    return new Envelope(
      from: env('SMTP_SYSTEM_EMAIL_ADDRESS', 'smtp@bspmi.com'),
      subject: "{$data->passenger->first_name} - your Booking Request for 70000TONS OF METAL 2026!"
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
