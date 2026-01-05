<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class LeadPassRemovesSomeone extends Notification {
    use Queueable;

    protected $bookingCode;
    protected $survivorNumber;
    protected $removedEmail;
    protected $urlConstruct;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $bookingCode, string $survivorNumber, string $removedEmail, object $booking) {
        $this->bookingCode = $bookingCode;
        $this->survivorNumber = $survivorNumber;
        $this->removedEmail = $removedEmail;
        $this->urlConstruct =
            config('app.url') . '/events/' . $booking->event_id . '/bookings/' . $booking->booking_code;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array {
        return ['slack'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toSlack(object $notifiable) {
        return (new SlackMessage())
            ->warning()
            ->content(':sweating_jordan_peele: Oh no! someone is kicked out')
            ->attachment(function ($attachment) {
                $attachment->title('Lead passenger remove someone')->fields([
                    'Booking code' => $this->bookingCode,
                    'Lead Pass Survivor Number' => $this->survivorNumber,
                    'Who was removed' => $this->removedEmail,
                    'URL to booking' => $this->urlConstruct,
                ]);
            });
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array {
        return [
                //
            ];
    }
}
