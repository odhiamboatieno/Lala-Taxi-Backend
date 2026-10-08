<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;

class CorporateResetPassword extends ResetPassword
{
    protected function resetUrl($notifiable)
    {
        return route('corporate.portal.password.reset', [
            'token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
