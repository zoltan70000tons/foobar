<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contract\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable {
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data) {
        $this->data = $data;
    }

    public function build() {
        $trailling = $this->subject('New  Contact Form Submission')->view('emails.contact-form');
        dd($trailling);
    }
}
