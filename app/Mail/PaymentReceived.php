<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReceived extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $passengerName;
    public $paymentAmount;
    public $bookingCode;

    /**
     * Create a new message instance.
     */
    public function __construct(string $passengerName, string $paymentAmount, string $bookingCode)
    {
        $this->passengerName = $passengerName;
        $this->paymentAmount = $paymentAmount;
        $this->bookingCode = $bookingCode;
        $this->onQueue('emails');
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $mailFromAddress = env('SMTP_SYSTEM_EMAIL_ADDRESS');

        return new Envelope(
            from: $mailFromAddress,
            subject: __('systemEmails.payment_received_subject')
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-received',
            with: [
                'passengerName' => $this->passengerName,
                'paymentAmount' => $this->paymentAmount,
                'bookingCode' => $this->bookingCode,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}