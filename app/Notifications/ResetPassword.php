<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends ResetPasswordNotification
{
    use Queueable, SerializesModels;

    public function toMail($notifiable)
    {
        return parent::toMail($notifiable)->subject('Your Password Reset Link');
    }
}
