<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\SlackMessage;

use App\Models\User;

class NewUserRegistered extends Notification {
    use Queueable;

    protected $email;

    /**
     * Create a new notification instance.
     */
    public function __construct(User $user) {
        $this->email = $user->email;
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
            ->success()
            ->content(':marilyn-manson: Welcome to the dark side')
            ->attachment(function ($attachment) {
                $attachment
                    ->title('New User Registered')
                    ->fields([
                        'Email' => $this->email,
                    ])
                    ->color('#4B0082');
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
