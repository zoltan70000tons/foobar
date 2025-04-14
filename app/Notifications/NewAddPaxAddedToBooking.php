<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class NewAddPaxAddedToBooking extends Notification
{
  use Queueable;

  protected $bookingCode;
  protected $email;
  protected $message;
  protected $urlConstruct;

  /**
   * Create a new notification instance.
   */
  public function __construct(string $bookingCode, string $email, ?string $message, object $booking)
  {
    $this->bookingCode = $bookingCode;
    $this->email = $email;
    $this->message = $message ?? '';
    $this->urlConstruct = config('app.url') . '/events/' . $booking->event_id . '/bookings/' . $booking->booking_code;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['slack'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toSlack(object $notifiable)
  {
    return (new SlackMessage())
      ->success()
      ->content(':vibe: Lets go!')
      ->attachment(function ($attachment) {
        $attachment->title('New add pax added to booking!')->fields([
          'Booking Code' => $this->bookingCode,
          'Email of new pax' => $this->email,
          'Additional message' => $this->message,
          'URL to booking' => $this->urlConstruct,
        ]);
      });
  }

  /**
   * Get the array representation of the notification.
   *
   * @return array<string, mixed>
   */
  public function toArray(object $notifiable): array
  {
    return [
        //
      ];
  }
}
