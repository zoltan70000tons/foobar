<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Config;

class CustomerResetPassword extends Notification
{
  use Queueable;

    /**
     * The password reset token.
     */
    public $token;

    /**
     * The callback that should be used to create the reset password URL.
     */
    public static $createUrlCallback;

    /**
     * Create a notification instance.
     */
    public function __construct($token)
    {
        $this->token = $token;
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

  public function toMail($notifiable)
  {
      if (static::$createUrlCallback) {
          $resetUrl = call_user_func(static::$createUrlCallback, $notifiable, $this->token);
      } else {
          $frontendUrl = config('app.frontend_url') . '/en/password-reset';
          $resetUrl = $frontendUrl . '?token=' . $this->token . '&email=' . urlencode($notifiable->email);
      }

      return (new MailMessage)
        ->subject(Lang::get('Reset Password Notification'))
        ->line(Lang::get('You are receiving this email because we received a password reset request for your account.'))
        ->action(Lang::get('Reset Password'), $resetUrl)
        ->line(Lang::get('This password reset link will expire in :count minutes.', ['count' => Config::get('auth.passwords.customers.expire')]))
        ->line(Lang::get('If you did not request a password reset, no further action is required.'));
  }

  /**
   * Set a callback that should be used when creating the reset password button URL.
   */
  public static function createUrlUsing($callback)
  {
      static::$createUrlCallback = $callback;
  }

  /**
   * Get the mail representation of the notification.
   */
  // public function toMail($notifiable)
  // {

  //   return (new MailMessage)
  //     ->subject(__('Reset Your Password'))
  //     ->line(__('You are receiving this email because we received a password reset request for your account.'))
  //     ->action(__('Reset Password'), $this->resetUrl)
  //     ->line(__('This password reset link will expire in 15 minutes.'))
  //     ->line(__('If you did not request a password reset, no further action is required.'));
  // }


}
