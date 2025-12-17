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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;
use App\Traits\HandlePermissions;
use App\Notifications\PaymentIsReceived;
use Illuminate\Support\Facades\Notification;
use App\Mail\PaymentReceived;
use Illuminate\Support\Facades\Mail;

class NotificationController extends Controller
{
  use HandlePermissions;
  use ExceptionLogger;

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
      $request->merge($request->input('bookingEnginePayload', []));
      $validated = $request->validate([
        'BIP_ID' => 'required|string|max:50',
        'amount' => 'required|numeric|min:0',
        'bookingCode' => 'required|string|max:20',

        'paymentData' => 'required|array|min:1',
        'paymentData.*.passengerId' => 'required|integer',
        'paymentData.*.passengerName' => 'nullable|string|max:255',
        'paymentData.*.passengerOrder' => 'required|integer',
        'paymentData.*.paymentAmount' => 'required|numeric|min:0',
        'paymentData.*.amountOwing' => 'nullable|numeric|min:0',
        'paymentData.*.balance' => 'nullable|numeric',

        'billingData' => 'required|array',
        'billingData.city' => 'required|string|max:255',
        'billingData.name' => 'required|string|max:255',
        'billingData.email' => 'required|email|max:255',
        'billingData.notes' => 'nullable|string|max:500',
        'billingData.state' => 'nullable|string|max:100',
        'billingData.street' => 'required|string|max:255',
        'billingData.country' => 'required|string|size:2', // ISO country code
        'billingData.postalCode' => 'nullable|string|max:20',

        'type' => 'required|string|in:PAYMENT,REFUND',
        'notes' => 'nullable|string|max:255',
        'transaction_date' => 'required|date',
      ]);

      // Prevent duplicate BIP_ID processing
      if (Payment::where('BIP_ID', $validated['BIP_ID'])->exists()) {
        return response()->json(['error' => 'Duplicate transaction'], 409);
      }

      // Load booking by booking_code
      $booking = Booking::where('booking_code', $validated['bookingCode'])->first();
      if (!$booking) {
        return response()->json(['error' => "{$validated['bookingCode']} Booking not found"], 404);
      }

      $bookingId = $booking->id;
      $eventId = $booking->event_id;

      // Ensure all paymentData passengers belong to this booking
      $passengerIds = collect($validated['paymentData'])
        ->pluck('passengerId')
        ->unique()
        ->values();
      $passengersInBooking = Passenger::whereIn('id', $passengerIds)
        ->where('booking_id', $booking->id)
        ->pluck('id');
      if ($passengersInBooking->count() !== $passengerIds->count()) {
        $invalid = $passengerIds->diff($passengersInBooking);
        return response()->json(
          [
            'error' => 'One or more passengers do not belong to this booking',
            'invalidPassengerIds' => $invalid->values(),
          ],
          422
        );
      }

      $isSplit = $passengerIds->count() > 1;

      foreach ($validated['paymentData'] as $row) {
        $paxId = (int) $row['passengerId'];
        $amountForPax = (float) $row['paymentAmount'];

        Payment::create([
          'amount' => $amountForPax,
          'passenger_id' => $paxId,
          'BIP_ID' => $validated['BIP_ID'],
          'type' => $validated['type'],
          'notes' => $validated['notes'] ?? null,
          'transaction_date' => $validated['transaction_date'],
          'source' => 'SYSTEM',
          'splitAmount' => $isSplit,
        ]);

        // Sync passenger balance
        $this->paymentInfoService->syncBalance($paxId, $bookingId, $eventId);
      }

      DB::commit();

      $leadPassenger = $booking->passengers->firstWhere('lead_passenger', true);

      // Slack notification with lead passenger info
      try {
        Notification::route('slack', env('SLACK_BOOKING_ENGINE_NOTIFICATIONS'))->notify(
          new PaymentIsReceived($booking, $leadPassenger, $validated['amount'])
        );
      } catch (\Exception $e) {
        Log::warning('Slack notification failed: ' . $e->getMessage());
      }

      // If refund, stop here (no email)
      if ($validated['type'] === 'REFUND') {
        return response()->json(['message' => 'Refund processed successfully'], 200);
      }

      // Send payment received email to the payer (This can be someone outside the booking)
      $payerName = $validated['billingData']['name'];
      $payerEmail = $validated['billingData']['email'];
      $language = $leadPassenger->user->detail->language ?? 'en';
      app()->setLocale($language);
      $formattedAmount = formatCurrency($validated['amount'], false, $language, false);

      try {
        Mail::to($payerEmail)->send(
          new PaymentReceived($payerName, $formattedAmount, $validated['bookingCode'], $language)
        );
      } catch (\Exception $e) {
        Log::error('Failed to send payment received email', [
          'error' => $e->getMessage(),
          'booking_code' => $validated['bookingCode'],
          'email' => $payerEmail,
        ]);
        return response()->json(['error' => 'Payment processed but email failed to send'], 500);
      }

      return response()->json(['message' => 'Payment processed, email sent successfully'], 200);
    } catch (\Throwable $e) {
      \Log::error('Error processing payment', [
        'exception' => $e->getMessage(),
        'request' => $request->all(),
      ]);
      return response()->json(['error' => 'Unexpected error occurred: ' . $e->getMessage()], 500);
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
