<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;
use App\Models\User;

class CustomerRecoverAccount extends Notification
{
  use Queueable;

  protected $survivorNumber;

  /**
   * Create a new notification instance.
   */
  public function __construct(User $user)
  {
    $this->survivorNumber = $user->survivorNumber->survivor_number;
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
      ->content(':rock: Customer Recover Account')
      ->attachment(function ($attachment) {
        $attachment->title('New User Registered')->fields([
          'Survivor Number' => $this->survivorNumber,
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
