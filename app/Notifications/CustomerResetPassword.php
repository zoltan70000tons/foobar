<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

class CustomerResetPassword extends Notification
{
  use Queueable;

  protected $resetUrl;
  /**
   * Create a new notification instance.
   */
  public function __construct($resetUrl)
  {
    $this->resetUrl = $resetUrl;
  }

  /**
   * Get the notification's delivery channels.
   *
   * @return array<int, string>
   */
  public function via(object $notifiable): array
  {
    return ['mail'];
  }

  /**
   * Get the mail representation of the notification.
   */
  public function toMail($notifiable)
  {

    return (new MailMessage)
      ->subject(__('Reset Your Password'))
      ->line(__('You are receiving this email because we received a password reset request for your account.'))
      ->action(__('Reset Password'), $this->resetUrl)
      ->line(__('This password reset link will expire in 15 minutes.'))
      ->line(__('If you did not request a password reset, no further action is required.'));
  }
}
