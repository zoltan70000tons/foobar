<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\SlackMessage;
use Illuminate\Notifications\Notification;

class PaymentIsReceived extends Notification
{
  use Queueable;

  protected $booking;
  protected $passenger;
  protected $amount;
  protected $urlConstruct;

  /**
   * Create a new notification instance.
   */
  public function __construct(object $booking, object $passenger, $amount)
  {
    $this->booking = $booking;
    $this->passenger = $passenger;
    $this->amount = $amount;

    $this->urlConstruct =
      config('app.url') . '/events/' . $this->booking->event_id . '/bookings/' . $this->booking->booking_code;
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
    // uri events/1/bookings/8714LGVD-F14R

    return (new SlackMessage())
      ->success()
      ->content(':scarface: Money, money, money!')
      ->attachment(function ($attachment) {
        $attachment->title('Payment is received')->fields([
          'Booking Code' => $this->booking->booking_code,
          'From' => $this->passenger->email,
          'Amount' => $this->amount,
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
