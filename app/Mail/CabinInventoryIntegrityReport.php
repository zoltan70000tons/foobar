<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CabinInventoryIntegrityReport extends Mailable implements ShouldQueue {
    use Queueable, SerializesModels;

    public array $report;

    public function __construct(array $report) {
        $this->report = $report;
        $this->onQueue('emails');
    }

    public function envelope(): Envelope {
        $mailFromAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS');
        $runAt = $this->report['run_at'] ?? now()->toDateString();

        return new Envelope(from: $mailFromAddress, subject: 'Cabin Inventory Integrity Report - ' . $runAt);
    }

    public function content(): Content {
        return new Content(
            view: 'emails.cabin-inventory-integrity-report',
            with: [
                'report' => $this->report,
            ],
        );
    }

    public function attachments(): array {
        return [];
    }
}
