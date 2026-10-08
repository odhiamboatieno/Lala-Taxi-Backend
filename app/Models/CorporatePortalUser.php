<?php

namespace App\Models;

use App\Notifications\CorporateResetPassword;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

class CorporatePortalUser extends Authenticatable
{
    use Notifiable;

    protected $guarded = ['id'];
    protected $hidden = ['password', 'remember_token'];
    protected $attributes = ['session_version' => 1, 'status' => 'Active'];
    protected $casts = ['password' => 'hashed', 'password_set_at' => 'datetime', 'session_version' => 'integer'];

    public function account()
    {
        return $this->belongsTo(CompanyCorporateAccount::class, 'corporate_account_id');
    }

    public function canManageUsers(): bool { return $this->role === 'admin'; }
    public function canViewInvoices(): bool { return in_array($this->role, ['admin', 'finance'], true); }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new CorporateResetPassword($token));
    }
}
