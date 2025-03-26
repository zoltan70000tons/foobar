<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPassengerMail extends Mailable implements ShouldQueue
{
  use Queueable, SerializesModels;

  public $content;
  public $subjectLine;
  private array $customAttachments = [];

  public function __construct($subjectLine, $content, $attachments = [])
  {
    $this->subjectLine = $subjectLine;
    $this->content = $content;
    $this->customAttachments = $attachments;
    $this->onQueue('emails');
  }

  public function build()
  {
    $email = $this->html($this->content)->subject($this->subjectLine);

    foreach ($this->customAttachments as $file) {
        $email->attach(
            storage_path('app/' . $file['path']),
            [
                'as' => $file['original_name'],
                'mime' => $file['mime'],
            ]
        );
    }

    return $email;
  }
}
