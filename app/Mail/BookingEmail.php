<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailContent;
    public $attachments;
    public $subject;

    public function __construct($subject, $emailContent, $attachments = [])
    {
        $this->subject = $subject;
        $this->emailContent = $emailContent;
        $this->attachments = $attachments;
    }

    public function build()
    {
        $email = $this->subject($this->subject)
            ->html($this->emailContent);
            $attachments = is_array($this->attachments) ? $this->attachments : [$this->attachments];

            foreach ($attachments as $file) {
                $email->attachData(
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName(),
                    ['mime' => $file->getMimeType()]
                );
            }
        return $email;
    }

}
