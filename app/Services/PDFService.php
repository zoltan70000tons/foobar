<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\App;
use Log;
use View;
use NumberToWords\NumberToWords;
use Symfony\Component\Intl\Countries;

class PDFService
{


  public function generateBookingConfirmationPDF(Booking $booking)
  {
    try {
      $passengerRepository = app(\App\Repositories\PassengerRepository::class);
      $paymentService = new PaymentInfoService($passengerRepository);
      $paymentInfo = $paymentService->getPaymentInfo($booking);
      $path = public_path('70K_Logo_Claim_BW_HiRes.jpg');
      $logoData = base64_encode(file_get_contents($path));
      $logoSrc = 'data:image/jpeg;base64,' . $logoData;
      $dateIssued = formatDate($booking->created_at);
      $lastUpdated = formatDate($booking->passengers->max('updated_at')); //oldest updated_at date
      $headerHtml = View::make('pdf.header', [
        'logoSrc' => $logoSrc,
        'lastUpdated' => $lastUpdated,
        'dateIssued' => $dateIssued,
        'bookingCode' => $booking->booking_code,
        'cabinType' => $booking->cabin->cabinType->cabin_type,
        'cabinCategory' => $booking->cabin->category->title,
        'cabinNumber' => $booking->cabin->cabinSpec->cabin_number,
        'capacity' => $booking->cabin->category->spec->capacity,
        'event' =>$booking->event,
        'grandTotal' => $booking->getGrandTotal(),
      ])->render();


      $data = [
        'event' => $booking->event,
        'booking' => $booking,
        'booking_code' => $booking->booking_code,
        'passengers' => $booking->passengers,
        'user' => User::find($booking->user_id),
        'cabin' => $booking->cabin,
        'cabin_specs' => $booking->cabin->cabinSpec,
        'category_specs' => $booking->cabin->category->spec,
        'paymentInfo' => $paymentInfo,
        'addpax_url' => env('ADDPAX_URL', 'https://70000tons.com/addpax'),
      ];

      $html = View::make('pdf.booking_confirmation', ['header' => $headerHtml, 'data' => $data, 'paymentInfo' => $paymentInfo])->render();

      // Load HTML into DomPDF
      $pdf = FacadePdf::loadHTML($html)
        ->setPaper('letter', 'portrait') // Set page size and orientation
        ->setOptions(['defaultFont' => 'sans-serif',]); // Optional font config
      $pdf->render();

      $canvas = $pdf->getDomPDF()->getCanvas();
      $w = $canvas->get_width();
      $h = $canvas->get_height();

      $footerText = [
        "UMCruises International Ltd.",
        "Suite 205A Saffrey Square • Bank Lane and Bay Street • Nassau • BAHAMAS",
        "70000TONS OF METAL HOTLINE: North America TOLL FREE: 1 888 705 8667 • All other areas: +1 305 777 4878",
        "Fax: +1 302 336 2639 • eMail: booking@70000tons.com • www.70000tons.com",
        "",
        "Page {PAGE_NUM} of {PAGE_COUNT}"
      ];
      $font = $pdf->getFontMetrics()->getFont('Helvetica', 'normal');
      $size = 6;
      $lineHeight = 8;
      $y = $h - (count($footerText) * $lineHeight) - 10;

      for ($i = 0; $i < count($footerText); $i++) {
        $line = $footerText[$i];
        if ($i === count($footerText) - 1) {
          $canvas->page_text($w / 2, $y, $line, $font, $size, [0, 0, 0], 1);
        } else {
          $textWidth = $canvas->get_text_width($line, $font, $size);
          $x = ($w - $textWidth) / 2;
          $canvas->page_text($x, $y, $line, $font, $size, [0, 0, 0]);
        }
        $y += $lineHeight;
      }

      return $pdf;
    } catch (\Exception $e) {
      Log::error('PDF generation error: ' . $e->getMessage());
    }
  }

  public function generateInvoicePDF(Booking $booking, $language = 'en')
  {
    App::setLocale($language);

    try {
      // 1) Static bank details
      $bank = [
        'holder'            => env('BANK_HOLDER'),
        'holder_address_1'  => env('BANK_HOLDER_ADDRESS_1'),
        'holder_address_2'  => env('BANK_HOLDER_ADDRESS_2'),
        'holder_address_3'  => env('BANK_HOLDER_ADDRESS_3'),
        'bank_name'         => env('BANK_NAME'),
        'branch_address_1'  => env('BANK_BRANCH_ADDRESS_1'),
        'branch_address_2'  => env('BANK_BRANCH_ADDRESS_2'),
        'branch_address_3'  => env('BANK_BRANCH_ADDRESS_3'),
        'account_no'        => env('BANK_ACCOUNT_NO'),
        'routing'           => env('BANK_ROUTING'),
        'swift'             => env('BANK_SWIFT'),
      ];

      // 2) Get booking details
      $logoUrl = 'https://70000tons.com/wp-content/uploads/2019/04/70K_Logo_Claim_BW_HiRes.jpg';
      $logoData = base64_encode(file_get_contents($logoUrl));
      $logoSrc = 'data:image/jpeg;base64,' . $logoData;

      $passengers    = $booking->passengers;
      $numPax        = $passengers->count();

      // 3) Totals
      $grandTotal    = $booking->getGrandTotal();                   // e.g. 12306.20
      $dollars       = floor($grandTotal);                          // 12306
      $cents         = round(($grandTotal - $dollars) * 100);       // 20
      $pricePerPax   = $grandTotal / max(1, $numPax);

      // 4) Format numbers
      $fmtNumber    = fn($n) => number_format($n, 2, '.', ',') . ' USD';
      $formatted    = [
        'price_per_pax'      => $fmtNumber($pricePerPax),
        'total'              => $fmtNumber($grandTotal),
        'invoice_amount'     => $fmtNumber($grandTotal),
        'amount_due_now'     => $fmtNumber($grandTotal),
      ];

      // 5) Number-to-words
      $numberToWords = new NumberToWords();
      $transformer   = $numberToWords->getNumberTransformer($language);
      $words         = ucwords($transformer->toWords($dollars));
      $amountInWords = "{$words} {$cents}/00;";

      $data = [
        'event' => $booking->event,
        'booking_code' => $booking->booking_code,
        'leadPassenger' => $booking->passengers->where('lead_passenger', true)->first(),
        'language' => $language,
        'logo' => $logoSrc,
        'bank' => $bank,
        'today' => now()->format('F d, Y'),
        'formatted' => $formatted,
        'amountInWords' => $amountInWords,
        'numPax' => $numPax,
        'grandTotal' => $grandTotal,
      ];

      // 6) Render HTML → PDF
      $html = View::make('pdf.invoice', $data)->render();

      // 7) Load HTML into DomPDF
      $pdf = FacadePdf::loadHTML($html)
        ->setPaper('letter', 'portrait') // Set page size and orientation
        ->setOptions(['defaultFont' => 'sans-serif',]); // Optional font config
      $pdf->render();

      // 8) Add the same multi-line footer you use already
      $canvas = $pdf->getDomPDF()->getCanvas();
      $w      = $canvas->get_width();
      $h      = $canvas->get_height();


      $footer = [
        "UMCruises International Ltd.",
        "Suite 205A Saffrey Square • Bank Lane and Bay Street • Nassau • BAHAMAS",
        "Tel.: +1 305 777 4878 • Fax: +1 302 336 2639 • eMail: info@70000tons.com"
      ];
      $font      = $pdf->getFontMetrics()->getFont('Helvetica', 'normal');
      $size      = 9;
      $lineHeight = 11;
      $y         = $h - (count($footer) * $lineHeight) - 10;

      foreach ($footer as $line) {
        $textWidth = $canvas->get_text_width($line, $font, $size);
        $x = ($w - $textWidth) / 2;
        $canvas->page_text($x, $y, $line, $font, $size, [0, 0, 0]);
        $y += $lineHeight;
      }

      return $pdf;
    } catch (\Exception $e) {
      Log::error('Invoice PDF generation error: ' . $e->getMessage());
      throw $e;
    }
  }

  public function getConfirmationPDFHeader(Booking $booking)
  {
    return View::make('pdf.header', [])->render();
  }
}
