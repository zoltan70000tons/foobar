<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;
use App\Mail\BookingEmail;
use App\Models\Booking;
use App\Models\Passenger;
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
        $validated = $request->validate([
            'lang' => 'required|string|in:en,es,fr,de',
            'template_name' => 'required|string|exists:email_templates,name',
            'email_content' => 'required|string',
            'template_id' => 'required|integer',
            'event_id' => 'required|integer',
            'subject' => 'required|string',
            'booking_id' => 'required|integer|exists:bookings,id',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120', // Máx. 5MB per file
        ]);

        try {

            return $this->withPermission(
                [Permissions::ViewBookings],
                function ($validated,$request) {
                    $booking = Booking::find($validated['booking_id']);
                    $passengers = $booking->passengers;

                    $attachments = collect($request->file('attachments', []))
                        ->filter(fn($file) => $file instanceof \Illuminate\Http\UploadedFile)
                        ->values()
                        ->all();

                    foreach ($passengers as $passenger) {
                        Mail::send([], [], function ($message) use ($passenger, $validated, $attachments) {
                            $message->to($passenger->email)
                                ->subject($validated['subject'])
                                ->html($validated['email_content']);
                            foreach ($attachments as $file) {
                                $message->attachData(
                                    file_get_contents($file->getRealPath()),
                                    $file->getClientOriginalName(),
                                    ['mime' => $file->getMimeType()]
                                );
                            }
                        });
                    }

                    $this->saveBookingLog($booking->id,'Email Sent to Costumer', $validated['subject']);
                    return response()->json(['message' => 'Emails sent successfully', 'success' => true], 200);
                },
                $validated,
                $request
            );

        } catch (\Exception $ex) {
            Log::info('Error sending email', ['error' => $ex->getMessage(), 'line' => $ex->getLine(), 'file' => $ex->getFile()]);
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
            'booking_id' => 'required|integer'
        ])->validate();
        $htmlContent = $this->emailTemplateService->getProcessedTemplate($validated['booking_id'], $validated['lang'], $validated['template_id'], []);
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

    // public function showEmail(Request $request)
    // {
    //     $booking_id = $request->input('id');
    //     $booking = Booking::find($booking_id);
    //     $service = new PDFService();
    //     $paymentService = new PaymentInfoService();
    //     $paymentService->syncAllocatedCost($booking, Passenger::find(1));

    //     $pdf = $service->generateBookingConfirmationPDF($booking);
    //     $pdf->setPaper('letter', 'potrait');
    //     return $pdf->stream();
    // }

    public function generateBookingPDF(Request $request)
    {
        $validated = Validator::make($request->all(), [
            // 'lang' => 'required|string|in:en,es,de',
            'booking_id' => 'required|integer'
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
            'booking_id' => 'required|integer'
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

            $mimeType = get_headers($eventImage, 1)["Content-Type"] ?? 'image/jpeg';


            return response($imageData, 200)->header("Content-Type", $mimeType);
        } catch (\Throwable $th) {
            return response()->json(['error' => 'Error retrieving image'], 500);
        }
    }
}
