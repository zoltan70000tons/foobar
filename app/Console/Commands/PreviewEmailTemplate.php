<?php

namespace App\Console\Commands;

use App\Jobs\SendEmailJob;
use Illuminate\Console\Command;
use App\Models\Booking;
use App\Services\EmailTemplateService;

class PreviewEmailTemplate extends Command {
    protected $signature = 'email:preview 
    {booking_id : The booking ID}
    {template_id : The email template ID}
    {--email= : Optional email to send the preview}
    {--open : Open the generated HTML file in Google Chrome}';

    protected $description = 'Displays the processed email template HTML for a booking and optionally opens it in Google Chrome';

    public function handle(EmailTemplateService $emailTemplateService) {
        $bookingId = $this->argument('booking_id');
        $templateId = $this->argument('template_id');
        $booking = Booking::find($bookingId);
        $optionalEmail = $this->option('email');

        if (!$booking) {
            $this->error('Booking not found');
            return Command::FAILURE;
        }

        $passenger = $booking->passengers()->first();

        if (!$passenger) {
            $this->error('No passengers found for this booking');
            return Command::FAILURE;
        }

        $html = $emailTemplateService->getProcessedTemplate($bookingId, $templateId, $passenger, []);

        if (!$html) {
            $this->error('Template not found or returned empty output');
            return Command::FAILURE;
        }

        $this->info('=== HTML OUTPUT ===');
        $this->line($html);

        $path = storage_path("app/email_preview_{$bookingId}_template_{$templateId}.html");
        file_put_contents($path, $html);

        $this->info("\nSaved to: $path");

        if ($this->option('open')) {
            $this->info('Opening in Google Chrome...');

            $escapedPath = escapeshellarg($path);

            if (PHP_OS_FAMILY === 'Darwin') {
                // macOS
                exec("open -a \"Google Chrome\" $escapedPath");
            } elseif (PHP_OS_FAMILY === 'Windows') {
                // Windows
                exec("start chrome $escapedPath");
            } else {
                // Linux
                exec("google-chrome $escapedPath || chromium $escapedPath");
            }
        }

        if ($optionalEmail) {
            $this->info("Sending email to: $optionalEmail");
            $booking->setRelation(
                'passengers',
                $booking->passengers->map(function ($p) use ($optionalEmail) {
                    $p = clone $p;
                    $p->email = $optionalEmail;
                    return $p;
                }),
            );

            $subject = 'Test Email Preview';

            try {
                SendEmailJob::dispatchSync(
                    $templateId,
                    $booking,
                    $passenger,
                    $attachments = [],
                    $extraData = [],
                    $bookingPdf = false,
                    $eventImage = false,
                    $ticketContract = false,
                    $emailContent = '',
                    $subject = '',
                    $to = [$optionalEmail],
                );
                $this->info('Email sent!');
            } catch (\Exception $e) {
                $this->error('Failed to send email: ' . $e->getMessage());
            }
        }

        return Command::SUCCESS;
    }
}
