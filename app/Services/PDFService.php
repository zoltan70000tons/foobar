<?php

namespace App\Services;

use App\Models\Booking;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf as FacadePdf;
use Illuminate\Support\Facades\DB;
use View;

class PDFService
{
  

    public function generateBookingConfirmationPDF(Booking $booking)
    {
        $passengerRepository = app(\App\Repositories\PassengerRepository::class);
        $paymentService = new PaymentInfoService($passengerRepository);
        $paymentInfo = $paymentService->getPaymentInfo($booking);
        $logoUrl = 'https://70000tons.com/wp-content/uploads/2019/04/70K_Logo_Claim_BW_HiRes.jpg';
        $logoData = base64_encode(file_get_contents($logoUrl));
        $logoSrc = 'data:image/jpeg;base64,' . $logoData;

        $data = [
            'event' => $booking->event,
            'booking' => $booking,
            'booking_code' => $booking->booking_code,
            'passengers' => $booking->passengers,
            'user' => User::find($booking->user_id),
            'cabin' => $booking->cabin,
            'cabin_specs' => $booking->cabin->cabinSpec,
            'cabin_type' => $booking->cabin->cabinType,
            'cabin_category' => $booking->cabin->category,
            'category_specs' => $booking->cabin->category->spec,
            'logo' => $logoSrc,
        ];

        $html = View::make('pdf.booking_confirmation', ['data' => $data, 'paymentInfo' => $paymentInfo])->render();

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
    }
}
