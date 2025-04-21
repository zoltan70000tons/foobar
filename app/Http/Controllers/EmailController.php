<?php

namespace App\Http\Controllers;

use App\Enums\Permissions;

use App\Models\Booking;
use App\Models\Passenger;
use App\Services\EmailTemplateService;
use App\Services\PDFService;
use App\Traits\BookingLogTrait;
use App\Traits\HandlePermissions;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Log;

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
            'selected_email' => 'required|email',
            'selected_pass_id' => 'required_if:selected_email,true|integer',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf|max:5120', // Máx. 5MB per file
            'booking_pdf' => 'required|boolean',
            'booking_image' => 'required|boolean'
        ]);
        try {
            return $this->withPermission(
                [Permissions::ViewBookings, Permissions::SendEmail],
                function ($validated, $request) {
                    $booking = Booking::find($validated['booking_id']);
                    $content = $validated['email_content'];
                    $subject = $validated['subject'];
                    $templateId = $validated['template_id'];
                    $preparedAttachments = [];
                    $pdf = $validated['booking_pdf'];
                    $image = $validated['booking_image'];
                    $passenger = Passenger::where('id', $validated['selected_pass_id'])
                        ->where('email', '=', $validated['selected_email'])
                        ->where('booking_id', $validated['booking_id'])
                        ->first();
                    if ($passenger) {
                        foreach ($request->file('attachments', []) as $file) {
                            $path = $file->store('temp_attachments');
                            $preparedAttachments[] = [
                                'path' => $path,
                                'name' => $file->getClientOriginalName(),
                                'original_name' => $file->getClientOriginalName(),
                                'mime' => $file->getMimeType(),
                            ];
                        }
                        $this->emailTemplateService->sendEmail(
                            $templateId,
                            $booking,
                            $passenger,
                            $preparedAttachments,
                            [],
                            $pdf,
                            $image,
                            false,
                            $content,
                            $subject
                        );

                        $this->saveBookingLog($booking->id, 'Email Sent to Costumers', $validated['subject']);
                        return response()->json(['message' => 'Emails sent successfully', 'success' => true], 200);
                    }
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
            'passenger_id' => 'required|integer',
        ])->validate();
        $passenger = Passenger::find($validated['passenger_id']);

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

    //for testing

    public function showEmail(Request $request)
    {
    //   $booking_id = $request->input('id');
    //   $passenger_id = $request->input('passenger_id');
    //   $booking = Booking::find($booking_id);
    //   $passenger = Passenger::find($passenger_id);
    //   if (!$booking || !$passenger) {
    //       return response()->json(['error' => 'Booking or Passenger not found'], 404);
    //   }
    //   $service = new PDFService();
    //    $pdf = $service->generateBookingConfirmationPDF($booking);
    //    $pdf->setPaper('letter', 'potrait');
    //    return $pdf->stream();

        // $htmlContent = $this->emailTemplateService->getProcessedTemplate(
        //     $booking_id,
        //     77,
        //     $passenger,
        //     []
        // );
        // if (!$htmlContent) {
        //     return response()->json(['error' => 'Template not found'], 404);
        // }
        // echo $htmlContent;
        //return response()->json(['html' => $htmlContent]);
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
