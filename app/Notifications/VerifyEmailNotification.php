<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class VerifyEmailNotification extends VerifyEmail
{
    public function toMail($notifiable): MailMessage
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Подтверждение email для AVA VPN')
            ->view('emails.verify-email', [
                'verificationUrl' => $verificationUrl,
                'userName' => $notifiable->name,
                'email' => $notifiable->email,
            ]);
    }
}
