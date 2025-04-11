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
  protected $payment;

  /**
   * Create a new notification instance.
   */
  public function __construct(object $booking, object $passenger, object $payment)
  {
    $this->booking = $booking;
    $this->passenger = $passenger;
    $this->payment = $payment;
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
      ->content(':scarface: Money, money, money!')
      ->attachment(function ($attachment) {
        $attachment->title('Payment is received')->fields([
          'Booking ID' => $this->booking->id,
          'From' => $this->passenger->email,
          'Value' => $this->payment->value,
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
