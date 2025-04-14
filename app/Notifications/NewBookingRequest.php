<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class NewBookingRequest extends Notification
{
  use Queueable;

  protected $bookingRequestId;
  protected $survivorNumber;
  protected $email;
  protected $cabinType;
  protected $urlConstruct;

  /**
   * Create a new notification instance.
   */
  public function __construct(
    string $bookingRequestId,
    string $survivorNumber,
    string $email,
    string $cabinType,
    object $booking
  ) {
    $this->bookingRequestId = $bookingRequestId;
    $this->survivorNumber = $survivorNumber;
    $this->email = $email;
    $this->cabinType = $cabinType;
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
      ->content(':borat: Very Nice!')
      ->attachment(function ($attachment) {
        $attachment
          ->title('New Booking Request!')
          ->fields([
            'Booking Request ID' => $this->bookingRequestId,
            'Survivor Number' => $this->survivorNumber,
            'Email' => $this->email,
            'Cabin Type' => $this->cabinType,
            'URL to booking' => $this->urlConstruct,
          ])
          ->color('#f525b6');
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
