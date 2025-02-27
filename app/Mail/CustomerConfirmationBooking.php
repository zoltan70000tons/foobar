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

  public $result;
  public $passenger;
  public $cart;
  public $language;

  public function __construct(array $result, array $passengerData, array $cart, string $language)
  {
    $this->result = $result;
    $this->passenger = $passengerData;
    $this->cart = $cart;
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

    $booking = $this->result['booking']['App\\Models\\Booking'];
    $cabin = $booking['cabin'];
    $category = $cabin['category'];

    return (object) [
      'passenger' => (object) [
        'first_name' => $this->passenger['first_name'] ?? 'N/A',
        'middle_name' => $this->passenger['middle_name'] ?? 'N/A',
        'last_name' => $this->passenger['last_name'] ?? 'N/A',
        'email' => $this->passenger['email'] ?? 'N/A',
        'gender' => $this->passenger['gender'] ?? 'N/A',
        'date_of_birth' => $this->passenger['dob'] ?? 'N/A',
        'citizenship' => $this->passenger['citizenship'] ?? 'N/A',
        'address_line_1' => $this->passenger['address_first'] ?? 'N/A',
        'address_line_2' => $this->passenger['address_second'] ?? 'N/A',
        'city' => $this->passenger['city'] ?? 'N/A',
        'state' => $this->passenger['state'] ?? 'N/A',
        'postal_code' => $this->passenger['postal_code'] ?? 'N/A',
        'country' => $this->passenger['country'] ?? 'N/A',
        'phone_number' => $this->passenger['phone'] ?? 'N/A',
        'emergency_contact_name' => $this->passenger['emergency_c_name'] ?? 'N/A',
        'emergency_phone_number' => $this->passenger['emergency_c_phone'] ?? 'N/A',
        'special_request' => $this->passenger['special_request'] ?? 'N/A',
        'survivor_referal_number' => $this->passenger['referral_details'] ?? 'N/A',
        'how_did_you_hear_about_us' => $this->passenger['hear_about'] ?? 'N/A',
        'receive_newsletter' => $this->passenger['newsletter'] ?? 'N/A',
        'receive_partner_information' => $this->passenger['travel_info'] ?? 'N/A',
        'accept_bed_configuration' => $this->passenger['cabin_conf_accp'] ? 'YES' : 'N/A',
        'accept_terms' => $this->passenger['terms_n_cons'] ? 'YES' : 'N/A',
      ],
      'booking' => (object) [
        'booking_type' => $booking['booking_code'] ?? 'N/A',
        'cabin_category' => $category['category_name'] ?? 'N/A',
        'form_of_payment' => $booking['payment_plan'] ?? 'N/A',
        'official_ticket_price_per_person' => number_format($this->cart['cabin_price'] ?? 0, 2),
        'pay_in_full_discount' => $category['discount'] ?? 0,
        'net_ticket_price_per_person' => $this->calculateNetTicketPrice(
          $this->cart['cabin_price'],
          $this->cart['price_save'],
          $this->cart['cabin_capacity']
        ),
        'taxes_and_fees_per_person' => $this->cart['price_extras'] ?? 'N/A',
        'single_traveler_surcharge' => $cabin['cabin_type']['cabin_type'] !== 'Private Cabin' ? '100' : 'N/A',
        'total_ticket_price' => number_format($this->passenger['passenger_allocated_cost'] ?? 0, 2),
        'number_of_passengers' => $this->cart['cabin_capacity'] ?? 1,
        'grand_total_booking_price' => number_format($this->cart['price_total'] ?? 0, 2),
        // 'payment_schedule' => !empty($this->installments)
        //     ? collect($this->installments)
        //         ->map(
        //             fn($installment) => [
        //                 'due_date' => $installment['due_date'],
        //                 'amount' => number_format($installment['amount'], 2),
        //             ]
        //         )
        //         ->toArray()
        //     : 'N/A',

        'payment_schedule' => 'N/A',
        'todays_date' => now()->format('Y-m-d'),
        'booking_request_id' => $booking['booking_request_id'] ?? 'N/A',
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
