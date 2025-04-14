<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Passenger;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\User;
use App\Services\EmailTemplateService;
use App\Services\PaymentService;
use App\Services\PaymentInfoService;
use App\Traits\ExceptionLogger;
use App\Traits\BookingLogTrait;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Traits\HandlePermissions;
use App\Notifications\PaymentIsReceived;
use Illuminate\Support\Facades\Notification;

class NotificationController extends Controller
{
  use HandlePermissions;
  use ExceptionLogger;
  use BookingLogTrait;

  protected EmailTemplateService $emailService;
  protected PaymentService $paymentService;
  protected PaymentInfoService $paymentInfoService;

  public function __construct(
    EmailTemplateService $emailService,
    PaymentService $paymentService,
    PaymentInfoService $paymentInfoService
  ) {
    $this->emailService = $emailService;
    $this->paymentService = $paymentService;
    $this->paymentInfoService = $paymentInfoService;
  }

  /**
   * Handle payment, send payment confirmation email, and log the transaction.
   *
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function sendPaymentEmail(Request $request)
  {
    DB::beginTransaction();

    try {
      $validated = $request->validate([
        'amount' => 'required|numeric|min:0',
        'passenger_id' => 'required|exists:passengers,id',
        'BIP_ID' => 'required|string|max:50',
        'type' => 'required|string|in:PAYMENT,REFUND',
        'notes' => 'nullable|string|max:255',
        'transaction_date' => 'required|date',
      ]);

      // Prevent duplicate BIP_ID processing
      if (Payment::where('BIP_ID', $validated['BIP_ID'])->exists()) {
        return response()->json(['error' => 'Duplicate transaction'], 409);
      }

      $validated['source'] = 'SYSTEM';

      $passenger = Passenger::find($validated['passenger_id']);
      $booking = $passenger->booking;

      if (!$booking) {
        return response()->json(['error' => 'No booking found for this passenger'], 404);
      }

      // Process payment
      Payment::create($validated);

      // Sync passeger balance
      $this->paymentInfoService->syncBalance($validated['passenger_id'], $booking->id, $booking->event_id);

      DB::commit();

      // send notification to slack
      try {
        Notification::route('slack', env('SLACK_BOOKING_ENGINE_NOTIFICATIONS'))->notify(
          new PaymentIsReceived($booking, $passenger, $validated['amount'])
        );
      } catch (\Exception $e) {
        // Optionally log the failure so you know something went wrong
        Log::warning('Slack notification failed: ' . $e->getMessage());
      }

      $this->saveBookingLog(
        $booking->id,
        'System Transaction Received',
        "System {$validated['type']} of \${$validated['amount']} was added to booking"
      );

      // Email template handling
      $template = match ($booking->payment_plan) {
        'PAY_IN_FULL' => 'thanks_payment_full',
        'INSTALLMENTS' => 'thanks_payment_inst',
        default => null,
      };

      if (!$template) {
        return response()->json(['error' => 'No template found for this payment plan'], 500);
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

      $extraData = ['PAID_AMOUNT' => formatCurrency($validated['amount'])];
      foreach ($booking->passengers as $passenger) {
        if (!empty($passenger->email) && filter_var($passenger->email, FILTER_VALIDATE_EMAIL)) {
          $this->emailService->sendEmail($templateId, $booking, $passenger, [], $extraData, true, true);
        }
      }
      return response()->json(['message' => 'Payment processed, email sent successfully'], 200);
    } catch (\Throwable $e) {
      \Log::error('Error processing payment', [
        'exception' => $e->getMessage(),
        'request' => $request->all(),
      ]);
      return response()->json(['error' => 'Unexpected error occurred'], 500);
    }
  }

  public function sendConfirmationEmail(Request $request)
  {
    $validated = $request->validate([
      'booking_id' => 'required|integer',
      'template' => 'required|string|in:+6000,-6000,single,invoice,new,comp,updated',
    ]);
    try {
      $booking = Booking::find($validated['booking_id']);
      if (!$booking) {
        \Log::warning('Booking not found', ['booking_id' => $validated['booking_id']]);
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
          \Log::error('No email template found', [
            'language' => $language,
            'template' => $validated['template'],
          ]);
          return response()->json(['error' => 'Email template not found'], 500);
        }
        if (!empty($passenger->email) && filter_var($passenger->email, FILTER_VALIDATE_EMAIL)) {
          $this->emailService->sendEmail($templateId, $booking, $passenger, [], [], true, true);
        }
      }
      return response()->json(['message' => 'Confirmation email sent successfully']);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json(['error' => 'Validation failed', 'details' => $e->errors()], 422);
    } catch (\Exception $e) {
      \Log::error('Error sending confirmation email', ['exception' => $e->getMessage()]);
      return response()->json(['error' => 'Internal server error'], 500);
    }
  }
}
