<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Services\EmailTemplateService;
use App\Services\MailService;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Validator;

class EmailController extends Controller
{

    protected $emailTemplateService;

    public function __construct(EmailTemplateService $emailTemplateService)
    {
        $this->emailTemplateService = $emailTemplateService;
    }

    /**
     * Sends an email with the specified template.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendEmail(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'lang' => 'required|string|in:en,es,fr,de',
            'template_name' => 'required|string|exists:email_templates,name',
            'email_content' => 'required|string',
            'event_id' => 'required|integer',
            'booking_id' => 'required|integer|exists:bookings,id',
        ])->validate();
        $booking = Booking::find($validated['booking_id']);
        $passengers = $booking->passengers;

        try {
            foreach ($passengers as $passenger) {
                Mail::html($validated['email_content'], function ($message) use ($validated, $passenger) {
                    $message->to($passenger->email) 
                            ->subject('Email from 70000TONS OF METAL');
                });
            }
        
            return response()->json(['message' => 'Emails sent successfully'], 200);

            //return response()->json(['message' => 'Email sent successfully'], 200);
        } catch (\Exception $e) {
            //throw $th;
            dd($e->getMessage());
        }
    }

    public function getEmailTemplates(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'lang' => 'required|string|in:en,es,de',
        ])->validate();

        $templates = DB::table('email_templates')
            ->where('lang', $validated['lang'])
            ->distinct()
            ->pluck('name')
            ->toArray();

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
            'template_name' => 'required|string|exists:email_templates,name',
        ])->validate();

        //$htmlContent = MailService::buildEmailTemplate(1, );
        $htmlContent = $this->emailTemplateService->getProcessedTemplate(1, $validated['lang'], $validated['template_name'], []);
        // $htmlContent = DB::table('email_templates')
        //     ->where('lang', $validated['lang'])
        //     ->where('name', $validated['template_name'])
        //     ->value('body');

        if (!$htmlContent) {
            return response()->json(['error' => 'Template not found'], 404);
        }

        $unlayerJson = [
            'body' => [
                'id' => uniqid('MJ_'),
                'rows' => [
                    [
                        'id' => uniqid('-cir_'),
                        'cells' => [1],
                        'columns' => [
                            [
                                'id' => uniqid('cle_'),
                                'contents' => [
                                    [
                                        'id' => uniqid('text_'),
                                        'type' => 'text',
                                        'values' => [
                                            'text' => $htmlContent,
                                            'containerPadding' => '0px',
                                        ],
                                    ],
                                ],
                                'values' => [],
                            ],
                        ],
                        'values' => [],
                    ],
                ],
                'values' => [
                    "contentAlign" => "center",
                    "contentWidth" => "100%",
                    'backgroundColor' => '#ffffff',
                    //   '_meta' => [
                    //     'htmlID' => 'u_body',
                    //     'htmlClassNames' => 'u_body',
                    //   ],
                ],
            ],
            'schemaVersion' => 12,
        ];

        return response()->json(['design' => $unlayerJson], 200, ['Content-Type' => 'application/json']);
    }
}
