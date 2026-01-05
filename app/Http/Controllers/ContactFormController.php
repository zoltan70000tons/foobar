<?php

namespace App\Http\Controllers;

use App\Mail\ContactFormMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class ContactFormController extends Controller {
    public function submit(Request $request) {
        $contactMail = env('MAIL_TO');
        $data = $request->validate([
            'fullName' => 'required|string',
            'email' => 'required|email',
            'confirmEmail' => 'required|same:email',
            'subject' => 'required|string',
            'comments' => 'required|string',
        ]);
        try {
            $response = Mail::send('emails.contact-form', ['data' => $data], function ($message) {
                $message->to('nicanor@70000tons.com')->subject('New Contact Form Submission');
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to send email. Please try again later.'], 500);
        }
    }
}
