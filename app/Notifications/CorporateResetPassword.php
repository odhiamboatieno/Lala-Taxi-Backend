<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class CorporateResetPassword extends ResetPassword
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject(__('corporate.portal_reset'))
            ->line(__('corporate.portal_email_intro'))
            ->action(__('corporate.portal_reset'), $this->resetUrl($notifiable))
            ->line(__('corporate.portal_email_expiry', ['minutes' => config('auth.passwords.corporate.expire')]))
            ->line(__('corporate.portal_email_ignore'));
    }

    protected function resetUrl($notifiable)
    {
        return route('corporate.portal.password.reset', [
            'token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset(),
        ]);
    }
}
