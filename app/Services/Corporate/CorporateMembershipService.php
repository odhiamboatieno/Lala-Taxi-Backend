<?php

namespace App\Services\Corporate;

use App\Models\Admin;
use App\Models\Company;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Modules\SaaSDispatch\Entities\CompanyCorporateAudit;

class CorporateMembershipService
{
    public function assign(CompanyCorporateAccount $account, User $rider, string $role, Admin|Company $actor): User
    {
        return DB::transaction(function () use ($account, $rider, $role, $actor) {
            $account = CompanyCorporateAccount::query()->lockForUpdate()->findOrFail($account->id);
            $rider = User::query()->lockForUpdate()->findOrFail($rider->id);
            $this->authorize($account, $rider, $actor);
            if (!in_array($role, ['member', 'manager', 'finance'], true) || $rider->user_type !== 'Rider'
                || $rider->status !== 'Active' || $account->status !== 'Active' || $account->archived_at
                || !$account->currency_code || !$account->legal_name || !$account->billing_address
                || ($rider->corporate_account_id && (int) $rider->corporate_account_id !== (int) $account->id)) {
                throw ValidationException::withMessages(['user_id' => __('corporate.invalid_membership')]);
            }
            if ((int) $rider->corporate_account_id === (int) $account->id && $rider->corporate_role === $role) {
                return $rider;
            }
            $previousRole = $rider->corporate_role;
            $rider->corporate_account_id = $account->id;
            $rider->corporate_role = $role;
            $rider->save();
            $this->audit($account, $rider, $actor, 'membership.assigned', ['previous_role' => $previousRole, 'role' => $role]);

            return $rider;
        }, 3);
    }

    public function remove(CompanyCorporateAccount $account, User $rider, Admin|Company $actor): void
    {
        DB::transaction(function () use ($account, $rider, $actor) {
            $account = CompanyCorporateAccount::query()->lockForUpdate()->findOrFail($account->id);
            $rider = User::query()->lockForUpdate()->findOrFail($rider->id);
            $this->authorize($account, $rider, $actor);
            abort_unless((int) $rider->corporate_account_id === (int) $account->id, 404);
            $oldRole = $rider->corporate_role;
            $rider->corporate_account_id = null;
            $rider->corporate_role = null;
            $rider->save();
            $this->audit($account, $rider, $actor, 'membership.removed', ['previous_role' => $oldRole]);
        }, 3);
    }

    private function authorize(CompanyCorporateAccount $account, User $rider, Admin|Company $actor): void
    {
        if ($actor instanceof Admin) {
            abort_unless($actor->hasPermission(['manage_company_accounts_invoicing']), 403);
        } else {
            abort_unless((int) $actor->id === (int) $account->company_id
                && (int) $rider->company_id === (int) $actor->id && operation_team_can('manage_company_accounts_invoicing'), 403);
        }
    }

    private function audit(CompanyCorporateAccount $account, User $rider, Admin|Company $actor, string $action, array $data): void
    {
        CompanyCorporateAudit::query()->create([
            'corporate_account_id' => $account->id,
            'action' => $action,
            'actor' => ($actor instanceof Admin ? 'admin:' : 'company:').$actor->id,
            'reference' => 'user:'.$rider->id,
            'data' => $data,
            'created_at' => now(),
        ]);
    }
}
