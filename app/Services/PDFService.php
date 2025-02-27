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
        $paymentService = new PaymentInfoService();
        $paymentInfo= $paymentService->getPaymentInfo($booking);
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
        
        $html = View::make('pdf.booking_confirmation', ['data' => $data, 'paymentInfo' =>$paymentInfo])->render();

        // Load HTML into DomPDF
        $pdf = FacadePdf::loadHTML($html)
            ->setPaper('a4', 'portrait') // Set page size and orientation
            ->setOptions(['defaultFont' => 'sans-serif']); // Optional font config

        return $pdf;
    }
}
