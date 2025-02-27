<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class BookingEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailContent;
    public $attachments;
    public $subject;

    public function __construct($subject, $content, $attachments)
    {
        $this->subject = $subject;
        $this->emailContent = $content;

        $this->attachments = [];
        foreach ($attachments as $file) {
            if ($file instanceof UploadedFile) {
                $this->attachments[] = $file;
            } else {
                \Log::warning("⚠️ Invalid file provided to BookingEmail", ['file' => $file]);
            
                throw new \InvalidArgumentException("Each attachment must be an instance of UploadedFile");
            }
        }
    }

    public function build()
    {
        $email = $this->subject($this->subject)
            ->html($this->emailContent);

        foreach ($this->attachments as $file) {
            if ($file instanceof UploadedFile) {
                $email->attachData(
                    file_get_contents($file->getRealPath()),
                    $file->getClientOriginalName(),
                    ['mime' => $file->getMimeType()]
                );
            }
           
        }

        return $email;
    }
}