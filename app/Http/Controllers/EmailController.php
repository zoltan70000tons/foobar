<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Mail\BookingEmail;
use App\Models\Booking;
use App\Models\Passenger;
use App\Models\User;
use App\Models\UserDetail;
use App\Repositories\PaymentRepository;
use App\Services\EmailTemplateService;
use App\Services\MailService;
use App\Services\PaymentInfoService;
use App\Services\PaymentService;
use App\Services\PDFService;
use App\Traits\BookingLogTrait;
use App\Traits\HandlePermissions;
use Blade;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Validator;
use Log;
use View;
use App\Mail\SendPassengerMail;

class EmailController extends Controller
{
  protected $emailTemplateService;

  use HandlePermissions;
  use BookingLogTrait;

  public function __construct(EmailTemplateService $emailTemplateService)
  {
    $this->emailTemplateService = $emailTemplateService;
  }

  public function sendEmail(Request $request)
  {
    Log::info('function first');
    $validated = $request->validate([
      'lang' => 'required|string|in:en,es,fr,de',
      'template_name' => 'required|string|exists:email_templates,name',
      'email_content' => 'required|string',
      'template_id' => 'required|integer',
      'event_id' => 'required|integer',
      'subject' => 'required|string',
      'booking_id' => 'required|integer|exists:bookings,id',
      'single_email' => 'required|boolean',
      'passenger_id' => 'required_if:single_email,true|integer',
      'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120', // Máx. 5MB per file
    ]);

    Log::info('function end');

    try {
      return $this->withPermission(
        [Permissions::ViewBookings],
        function ($validated, $request) {
          $booking = Booking::find($validated['booking_id']);
          $passengers = $booking->passengers;
          $content = $validated['email_content'];
          $subject = $validated['subject'];
          $preparedAttachments = [];
          foreach ($request->file('attachments', []) as $file) {
            $path = $file->store('temp_attachments');
            $preparedAttachments[] = [
              'path' => $path,
              'original_name' => $file->getClientOriginalName(),
              'mime' => $file->getMimeType(),
            ];
          }
          if ($validated['single_email'] == true) {
            $passenger = Passenger::find($validated['passenger_id']);
            Mail::to($passenger->email)->queue(
                new SendPassengerMail($subject, $content, $preparedAttachments)
            );
          } else {
            foreach ($passengers as $passenger) {
                Mail::to($passenger->email)->queue(
                    new SendPassengerMail($subject, $content, $preparedAttachments)
                );
            }
          }
          $this->saveBookingLog($booking->id, 'Email Sent to Costumer', $validated['subject']);
          return response()->json(['message' => 'Emails sent successfully', 'success' => true], 200);
        },
        $validated,
        $request
      );
    } catch (\Exception $ex) {
      Log::info('Error sending email', [
        'error' => $ex->getMessage(),
        'line' => $ex->getLine(),
        'file' => $ex->getFile(),
      ]);
      return response()->json(['message' => 'Error sending email', 'success' => false], 400);
    }
  }

  public function getEmailTemplates(Request $request)
  {
    $validated = Validator::make($request->all(), [
      'lang' => 'required|string|in:en,es,de',
    ])->validate();

    $templates = DB::table('email_templates')
      ->select(['id', 'name', 'lang', 'subject'])
      ->where('lang', $validated['lang'])
      ->distinct()
      ->get();

    return response()->json(['templates' => $templates]);
  }

  /**
   * Returns the content of the specified email template.
   *
   * @param \Illuminate\Http\Request $request
   * @return \Illuminate\Http\JsonResponse
   */
  public function getEmailTemplate(Request $request)
  {
    $validated = Validator::make($request->all(), [
      'lang' => 'required|string|in:en,es,de',
      'template_id' => 'required|integer',
      'booking_id' => 'required|integer',
      'single_email' => 'required|boolean',
      'passenger_id' => 'required_if:single_email,true|integer',
    ])->validate();

    $passenger = null;
    if ($validated['single_email'] == '1') {
      $passenger = Passenger::find($validated['passenger_id']);
    }

    $htmlContent = $this->emailTemplateService->getProcessedTemplate(
      $validated['booking_id'],
      $validated['template_id'],
      $passenger,
      []
    );
    if (!$htmlContent) {
      return response()->json(['error' => 'Template not found'], 404);
    }

    $unlayerJson = [
      'body' => [
        'rows' => [
          [
            'cells' => [1],
            'columns' => [
              [
                'contents' => [
                  [
                    'type' => 'text',
                    'values' => [
                      'text' => $htmlContent,
                      'containerPadding' => '0px',
                    ],
                  ],
                ],
              ],
            ],
          ],
        ],
        'values' => [
          'contentWidth' => '100%',
          'backgroundColor' => '#ffffff',
        ],
      ],
      'schemaVersion' => 12,
    ];
    return response()->json(['design' => $unlayerJson]);
  }

  public function showEmail(Request $request)
  {
    $booking_id = $request->input('id');
    $booking = Booking::find($booking_id);
    $passenger = $booking->passengers->first();
    $lead = $booking->passengers->where('lead_passenger', true)->first();
    $user = User::where('email', $lead->email)->first();
    $detail = $user->detail;
    // dd($detail);
    $lang = $detail->language ?? 'en';
    dd($lang);
    // dd($passenger->getNextInstallmentAttribute());

    // //$service = new EmailTemplateService();
    try {
      $user = User::find('32065109-3d42-3736-aab6-c23b1b407c81'); // Cambia el ID por un usuario válido
      $token = $user->createToken('API Token')->plainTextToken;

      echo $token;
      $service = new EmailTemplateService();
      $bookingConfirmationTemplate = $service->getProcessedTemplate($booking_id, 50, $passenger, [
        'PAID_AMOUNT' => 'USD 1000.00',
      ]);
      echo $bookingConfirmationTemplate;
      //$email = $service->sendEmail($bookingConfirmationTemplate,$booking, [], true, true);
      // dd($email);
    } catch (\Exception $e) {
      dd($e->getMessage());
    }

    $service = new PDFService();
    // paymentService = new PaymentInfoService();
    //$paymentService->syncAllocatedCost($booking, Passenger::find(1));

    // $pdf = $service->generateBookingConfirmationPDF($booking);
    // $pdf->setPaper('letter', 'potrait');
    // return $pdf->stream();
  }

  public function generateBookingPDF(Request $request)
  {
    $validated = Validator::make($request->all(), [
      // 'lang' => 'required|string|in:en,es,de',
      'booking_id' => 'required|integer',
    ])->validate();
    try {
      $booking = Booking::find($validated['booking_id']);
      $service = new PDFService();
      $pdf = $service->generateBookingConfirmationPDF($booking);
      return $pdf->stream();
    } catch (\Throwable $th) {
      //throw $th;
    }
  }

  public function generateBookingImg(Request $request)
  {
    $validated = Validator::make($request->all(), [
      'booking_id' => 'required|integer',
    ])->validate();

    try {
      $booking = Booking::findOrFail($validated['booking_id']);
      $eventImage = $booking->event->image;

      if (!filter_var($eventImage, FILTER_VALIDATE_URL)) {
        return response()->json(['error' => 'Invalid image URL'], 400);
      }

      $imageData = file_get_contents($eventImage);
      if (!$imageData) {
        return response()->json(['error' => 'Could not retrieve image'], 404);
      }

      $mimeType = get_headers($eventImage, 1)['Content-Type'] ?? 'image/jpeg';

      return response($imageData, 200)->header('Content-Type', $mimeType);
    } catch (\Throwable $th) {
      return response()->json(['error' => 'Error retrieving image'], 500);
    }
  }
}
