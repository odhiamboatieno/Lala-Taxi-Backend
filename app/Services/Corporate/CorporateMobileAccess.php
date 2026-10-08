<?php

namespace App\Services\Corporate;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

final class CorporateMobileAccess
{
    public function user(Request $request): User
    {
        $user = $request->attributes->get('verified_jwt_user');
        abort_unless($user instanceof User, 401);
        abort_unless($user->user_type === 'Rider' && $user->status === 'Active', 403);
        return $user;
    }

    public function account(User $user, bool $required = true): ?CompanyCorporateAccount
    {
        if (!$user->corporate_account_id) {
            abort_if($required, 403);
            return null;
        }
        abort_unless(in_array($user->corporate_role, ['member', 'manager', 'finance'], true), 403);
        return CompanyCorporateAccount::findOrFail($user->corporate_account_id);
    }

    public function capabilities(User $user): array
    {
        return ['view_dashboard' => true, 'view_account_trips' => in_array($user->corporate_role, ['manager', 'finance'], true),
            'view_users' => $user->corporate_role === 'manager',
            'view_invoices' => $user->corporate_role === 'finance' || ($user->corporate_role === 'manager' && config('corporate.manager_finance_access')),
            'view_credit' => in_array($user->corporate_role, ['manager', 'finance'], true)];
    }

    public function requireCapability(User $user, string $capability): void
    {
        abort_unless($this->capabilities($user)[$capability] ?? false, 403);
    }

    public function trips(CompanyCorporateAccount $account, User $user): Builder
    {
        return $account->trips()->getQuery()->where('billing_mode', 'corporate')
            ->when(!$this->capabilities($user)['view_account_trips'], fn ($query) => $query->where('user_id', $user->id));
    }
}
