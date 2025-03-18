<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Passenger;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\EmailTemplateService;
use App\Services\PaymentService;
use Illuminate\Support\Facades\Log;


class NotificationController extends Controller
{
    protected $emailService;
    protected $paymentService;

    public function __construct(EmailTemplateService $emailService, PaymentService $paymentService)
    {
        $this->emailService = $emailService;
        $this->paymentService = $paymentService;
    }

    /**
     * Handle payment notification and send confirmation email.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendPaymentEmail(Request $request)
    {

        $valid = $request->validate([
            'amount' => 'required|numeric|min:0',
            'passenger_id' => 'required|exists:passengers,id',
            'bip_id' => 'required|string|max:50',
            'type' => 'required|string|in:PAYMENT,REFUND',
            'notes' => 'nullable|string|max:255',
        ]);

        $template = null;

        $passenger = Passenger::find($request->passenger_id);

        if (!$passenger) {
            return response()->json(['error' => 'Passenger not found'], 404);
        }

        $booking = $passenger->booking;

        if (!$booking) {
            return response()->json(['error' => 'No booking associated with this passenger'], 404);
        }

        if ($booking->payment_plan === 'PAY_IN_FULL') {
            $template = 'thanks_payment_full';
        }

        if ($booking->payment_plan === 'INSTALLMENTS') {
            $template = 'thanks_payment_inst';
        }

        $lead = $booking->passengers->where('lead_passenger', true)->first();
        $user = User::where('email', $lead->email)->first();
        $detail = $user->detail;
        $language = $detail->language ?? 'en';
        $templateId = $this->emailService->getTemplateId($language, $template);

        if (!$templateId) {
            Log::error("No email template found for language: {$language}");
            return response()->json(['error' => 'Email template not found'], 500);
        }
        $data = $request->all();
        $data['source'] = 'SYSTEM';
        $result = $this->paymentService->processPayment($data);
        $extraData = ['PAID_AMOUNT' => formatCurrency($request->amount)];
        $emailSent = $this->emailService->sendEmail($templateId, $booking, $passenger,[],$extraData, false, false);
        if ($emailSent) {
            return response()->json(['message' => 'Payment confirmation email sent successfully']);
        } else {
            return response()->json(['error' => 'Failed to send email'], 500);
        }
    }


    public function sendConfirmationEmail(Request $request)
    {
        $validated = $request->validate([
            'booking_id' => 'required|integer',
            'template'  => 'required|string|in:+6000,-6000,single,invoice,new,comp,updated',
        ]);
        try {

            $booking = Booking::find($validated['booking_id']);
            if (!$booking) {
                \Log::warning("Booking not found", ['booking_id' => $validated['booking_id']]);
                return response()->json(['error' => 'Booking not found'], 404);
            }
            $passenger = $booking->passengers->first();
            $lead = $booking->passengers->where('lead_passenger', true)->first();
            $user = User::where('email', $lead->email)->first();
            $detail = $user->detail;
            $language = $detail->language ?? 'en';

            foreach ($booking->passengers as $passenger) {
                $templateId = $this->emailService->getTemplateId($language, $validated['template']);
    
                if (!$templateId) {
                    \Log::error("No email template found", [
                        'language' => $language,
                        'template' => $validated['template'],
                    ]);
                    return response()->json(['error' => 'Email template not found'], 500);
                }
                $this->emailService->sendEmail($templateId, $booking, $passenger, [], [], true, true, true);
            }
    
            return response()->json(['message' => 'Confirmation email sent successfully']);
    
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
        } catch (\Exception $e) {
            \Log::error("Error sending confirmation email", ['exception' => $e->getMessage()]);
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }
    


}
