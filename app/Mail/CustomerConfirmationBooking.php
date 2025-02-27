<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;

class CustomerConfirmationBooking extends Mailable
{
  use Queueable, SerializesModels;

  public $booking;
  public $cart;
  public $installments;
  public $language;

  public function __construct(Booking $booking, array $cart, array $installments, string $language)
  {
    $this->booking = $booking;
    $this->cart = $cart;
    $this->installments = $installments;
    $this->language = $language;
  }

  // PREPARE DATA FOR TEMPLATE
  private function prepareDataForTemplate()
  {
    // \Log::info('Result ----> data: ' . json_encode($this->booking));
    // \Log::info('Passenger ----> data: ' . json_encode($this->booking->passengers));
    // \Log::info('Cart ----> data: ', ['cart' => $this->cart]);

    // $passenger =
    //   collect($this->booking->passengers)->firstWhere('lead_passenger', true) ??
    //   collect($this->booking->passengers)->first();

    $booking = $this->booking;
    $cabin = $booking->cabin ?? null;
    $category = $cabin->category ?? null;
    $adjustments = $booking->adjustments ?? null;
    $passenger =
      collect($booking->passengers)->firstWhere('lead_passenger', true) ?? collect($booking->passengers)->first();

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
        'newsletter' => $passenger->newsletter === true ? 'YES' : 'NO',
        'receive_partner_information' => $passenger->travel_info === true ? 'YES' : 'NO',
        'accept_bed_configuration' => $passenger->cabin_conf_accp ? 'YES' : 'N/A',
        'accept_terms' => $passenger->terms_n_cons ? 'YES' : 'N/A',
      ],
      'booking' => (object) [
        'booking_type' => $this->getBookingType($this->cart['cabin_type']) ?? 'N/A',
        'cabin_category' => $category->category_name ?? 'N/A',
        'form_of_payment' => $passenger->payment_method ?? 'N/A',
        'official_ticket_price_per_person' => number_format($this->cart['cabin_price'] ?? 0, 2),
        'pay_in_full_discount' => $adjustments->where('code', 'PAID_IN_FULL')->first()->value ?? 'N/A',
        'net_ticket_price_per_person' => $this->calculateNetTicketPrice(
          $this->cart['cabin_price'],
          $this->cart['price_save']
        ),
        'taxes_and_fees_per_person' => number_format($this->cart['price_extras'] ?? 0, 2),
        'single_traveler_surcharge' => $adjustments->where('code', 'SINGLE_TICKET_FEE')->first()->value ?? 'N/A',
        'total_ticket_price' => number_format($passenger->passenger_allocated_cost ?? 0, 2),
        'number_of_passengers' => $this->cart['cabin_capacity'] ?? 1,
        'grand_total_booking_price' => number_format($this->cart['price_total'] ?? 0, 2),
        'payment_schedule' => empty($this->installments) ? 'PAID IN FULL' : 'N/A',
        'payment_schedule_installments' => !empty($this->installments)
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
        'booking_request_id' => $booking->booking_request_id ?? 'N/A',
      ],
    ];
  }

  // BOOKING TYPE
  private function getBookingType($bookingType)
  {
    if ($bookingType == 'single-male') {
      return 'Single Male';
    } elseif ($bookingType == 'single-female') {
      return 'Single Female';
    } else {
      return 'Private Cabin';
    }
  }

  // CALCULATE NET TICKET PRICE
  private function calculateNetTicketPrice($cabinPrice, $save)
  {
    // Convert all inputs to the correct types
    $cabinPrice = (float) $cabinPrice;
    $save = (float) $save;

    $netPrice = $cabinPrice - $save;

    return number_format($netPrice, 2, '.', '');
  }

  // envelope
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
