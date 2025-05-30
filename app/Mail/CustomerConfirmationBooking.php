<?php

namespace App\Mail;

use App;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Models\Booking;
use Illuminate\Support\Str;
use DateTime;
use IntlDateFormatter;

class CustomerConfirmationBooking extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  public $booking;
  public $cart;
  public $installments;
  public $language;
  public $event;


  public function __construct(Booking $booking, array $cart, array $installments, string $language, $event)
  {
    $this->booking = $booking;
    $this->cart = $cart;
    $this->installments = $installments;
    $this->language = $language;
    $this->event = $event;
    $this->onQueue('emails');
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
        'date_of_birth' => $this->getLocalizedDate($passenger->dob, $this->language) ?? 'N/A',
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
        'newsletter' => $passenger->newsletter == true ? 'YES' : 'NO',
        'receive_partner_information' => $passenger->travel_info == true ? 'YES' : 'NO',
        'accept_bed_configuration' => $passenger->cabin_conf_accp ? 'YES' : 'N/A',
        'accept_terms' => $passenger->terms_n_cons ? 'YES' : 'N/A',
      ],
      'booking' => (object) [
        'booking_type' => $this->getBookingType($this->cart['cabin_type']) ?? 'N/A',
        'cabin_category' => $category->title ?? 'N/A',
        'form_of_payment' => $passenger->payment_method == 'CREDIT_CARD' ? 'Credit Card' : 'Bank Transfer',
        'bed_config' => $booking->bed_config,
        'official_ticket_price_per_person' => number_format($this->cart['cabin_price'] ?? 0, 2),
        'pay_in_full_discount' => isset($adjustments->where('code', 'PAID_IN_FULL')->first()->value)
          ? intval($adjustments->where('code', 'PAID_IN_FULL')->first()->value)
          : 0,
        'choose_your_cabin' => number_format($adjustments->where('code', 'CHOOSE_YOUR_CABIN')->first()->value ?? 0, 2),
        'survivor_discount' => $this->event->status !== 'PUBLIC' ? $adjustments->firstWhere(fn($item) => Str::startsWith($item->code, 'MEMBERSHIP_'))?->value : 0,
        'carbon_offset' =>
        number_format($adjustments->firstWhere(fn($item) => Str::startsWith($item->code, 'CARBON_OFFSET'))?->value ?? 0, 2),
        'net_ticket_price_per_person' => $this->calculateNetTicketPrice(
          $this->cart['cabin_price'],
          $this->cart['price_save']
        ),
        'taxes_and_fees_per_person' => number_format($this->cart['tax'] ?? 0, 2, '.', ','),
        'single_traveler_surcharge' => $adjustments->where('code', 'SINGLE_TICKET_FEE')->first()->value ?? 'N/A',
        'total_ticket_price' => number_format($passenger->passenger_allocated_cost ?? 0, 2),
        'number_of_passengers' => $this->cart['cabin_type'] === 'private-cabin' ? $this->cart['cabin_capacity'] : 1,
        'grand_total_booking_price' => number_format($this->cart['price_total'] ?? 0, 2),
        'payment_schedule' => empty($this->installments) ? 'PAID IN FULL' : 'N/A',
        'payment_schedule_installments' => !empty($this->installments)
          ? collect($this->installments)
          ->map(
            fn($installment) => [
              'due_date' => $this->getLocalizedDate($installment['due_date'], $this->language),
              'amount' => number_format($installment['amount'], 2),
            ]
          )
          ->toArray()
          : 'N/A',
        'todays_date' => $this->getLocalizedDate(now(), $this->language),
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

  // GET LOCALIZED DATE
  private function getLocalizedDate($dateInput, $locale = 'en')
  {
    // Map short locales to full ICU locales
    $localeMap = [
      'en' => 'en_US',
      'de' => 'de_DE',
      'es' => 'es_ES',
    ];

    $icuLocale = $localeMap[$locale] ?? 'en_US';

    // Convert string to DateTime if needed
    if (!($dateInput instanceof DateTime)) {
      $dateInput = new DateTime($dateInput);
    }

    // Create a formatter
    $formatter = new IntlDateFormatter(
      $icuLocale,
      IntlDateFormatter::LONG,
      IntlDateFormatter::NONE,
      $dateInput->getTimezone()
    );

    return $formatter->format($dateInput);
  }

  // envelope
  public function envelope(): Envelope
  {
    App::setLocale($this->language);
    $data = $this->prepareDataForTemplate();


    \Log::info('Envelope ----> data: ' . json_encode($data));

    $mailFromAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS');
    $bccEmailAddress = env('MAIL_BCC');

    return new Envelope(
      from: $mailFromAddress,
      subject: "{$data->passenger->first_name}, " . __('confirmationBooking.cbe_subject') . " {$this->event->name}!",
      cc: [],
      bcc: [$bccEmailAddress]
    );
  }

  public function content(): Content
  {
    App::setLocale($this->language);

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
