<?php

namespace App\Services\Corporate;

use App\Models\Admin;
use App\Models\Company;
use App\Models\CorporatePortalUser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Modules\SaaSDispatch\Entities\CompanyCorporateAudit;

class CorporatePortalUserService
{
    public function authorize(CompanyCorporateAccount $account, Admin|Company|CorporatePortalUser $actor): void
    {
        if ($actor instanceof CorporatePortalUser) {
            abort_unless($actor->status === 'Active' && $actor->canManageUsers()
                && (int) $actor->corporate_account_id === (int) $account->id, 403);
        } else {
            app(CorporateFinanceAuthorization::class)->authorize($account, $actor);
        }
    }

    public function save(CompanyCorporateAccount $account, array $input, Admin|Company|CorporatePortalUser $actor, ?CorporatePortalUser $user = null): CorporatePortalUser
    {
        $this->authorize($account, $actor);
        abort_unless($account->status === 'Active' && !$account->archived_at, 409);
        if ($user) abort_unless((int) $user->corporate_account_id === (int) $account->id, 404);
        if (isset($input['email']) && is_string($input['email'])) {
            $input['email'] = Str::lower(trim($input['email']));
        }
        $data = validator($input, [
            'name' => 'required|string|max:255', 'email' => ['required', 'email:rfc', 'max:255', Rule::unique('corporate_portal_users')->ignore($user?->id)],
            'role' => 'required|in:admin,finance,viewer', 'status' => 'required|in:Active,Inactive',
        ])->validate();
        return DB::transaction(function () use ($account, $actor, $user, $data) {
            $locked = CompanyCorporateAccount::lockForUpdate()->findOrFail($account->id);
            $freshActor = $actor instanceof CorporatePortalUser ? $actor->fresh() : $actor;
            abort_unless($freshActor, 403);
            $this->authorize($locked, $freshActor);
            abort_unless($locked->status === 'Active' && !$locked->archived_at, 409);
            $existing = $user ? CorporatePortalUser::lockForUpdate()->findOrFail($user->id) : null;
            if ($existing) abort_unless((int) $existing->corporate_account_id === (int) $locked->id, 404);
            if ($existing && $existing->role === 'admin' && $existing->status === 'Active'
                && ($data['role'] !== 'admin' || $data['status'] !== 'Active')
                && CorporatePortalUser::where('corporate_account_id', $account->id)->where('role', 'admin')->where('status', 'Active')->count() <= 1) {
                throw ValidationException::withMessages(['role' => __('corporate.portal_last_admin')]);
            }
            $record = $existing ?? new CorporatePortalUser;
            $oldEmail = $existing?->email;
            $changedEmail = $existing && $existing->email !== $data['email'];
            $record->fill($data);
            $record->corporate_account_id = $account->id;
            if (!$existing || $changedEmail) {
                $record->password = Hash::make(Str::random(64));
                $record->password_set_at = null;
            }
            if ($existing && $record->isDirty()) $record->session_version++;
            $record->save();
            if ($changedEmail) DB::table('corporate_password_resets')->where('email', $oldEmail)->delete();
            CompanyCorporateAudit::create(['corporate_account_id' => $account->id,
                'action' => $existing ? 'portal.access_updated' : 'portal.invited',
                'actor' => $actor instanceof CorporatePortalUser ? 'portal:'.$actor->id : app(CorporateFinanceAuthorization::class)->identifier($actor),
                'reference' => 'portal:'.$record->id, 'data' => ['role' => $record->role, 'status' => $record->status], 'created_at' => now()]);
            return $record;
        }, 3);
    }

    public function invite(CorporatePortalUser $user): string
    {
        abort_unless($user->status === 'Active' && $user->account->status === 'Active' && !$user->account->archived_at, 409);
        return Password::broker('corporate')->sendResetLink(['email' => $user->email, 'status' => 'Active']);
    }
}
