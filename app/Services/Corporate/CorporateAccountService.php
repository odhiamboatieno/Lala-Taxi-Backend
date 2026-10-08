<?php

namespace App\Services\Corporate;

use App\Models\Admin;
use App\Models\Company;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Modules\SaaSDispatch\Entities\CompanyCorporateAudit;

class CorporateAccountService
{
    public function __construct(private CorporateFinanceAuthorization $authorization) {}

    public function create(Company $company, array $input, Admin|Company $actor): CompanyCorporateAccount
    {
        $this->authorization->authorizeOwner((int) $company->id, $actor);
        abort_unless(app(CorporateCapabilities::class)->managementEnabled($company), 404);
        $data = $this->validate($input);

        return DB::transaction(function () use ($company, $data, $actor) {
            $account = CompanyCorporateAccount::create($data + ['company_id' => $company->id]);
            $this->audit($account, $actor, 'account.created', $data);

            return $account;
        }, 3);
    }

    public function update(CompanyCorporateAccount $candidate, array $input, Admin|Company $actor): CompanyCorporateAccount
    {
        $this->authorization->authorize($candidate, $actor);
        $data = $this->validate($input, $candidate);

        return DB::transaction(function () use ($candidate, $data, $actor) {
            $account = CompanyCorporateAccount::lockForUpdate()->findOrFail($candidate->id);
            $this->authorization->authorize($account, $actor);
            if ($account->archived_at) {
                throw ValidationException::withMessages(['account' => __('corporate.account_archived')]);
            }
            if ($account->trips()->exists()) {
                foreach (['currency_code', 'billing_timezone', 'billing_cycle'] as $field) {
                    if ($data[$field] !== $account->$field) {
                        throw ValidationException::withMessages([$field => __('corporate.immutable_billing_settings')]);
                    }
                }
            }
            $before = $account->only(array_keys($data));
            $account->fill($data);
            if ($account->isDirty()) {
                $account->save();
                $this->audit($account, $actor, 'account.updated', ['before' => $before, 'after' => $data]);
            }

            return $account;
        }, 3);
    }

    public function archive(CompanyCorporateAccount $candidate, Admin|Company $actor): CompanyCorporateAccount
    {
        return DB::transaction(function () use ($candidate, $actor) {
            $account = CompanyCorporateAccount::lockForUpdate()->findOrFail($candidate->id);
            $this->authorization->authorize($account, $actor);
            if (!$account->archived_at) {
                $account->update(['status' => 'Inactive', 'archived_at' => now('UTC'), 'archived_by' => $this->authorization->identifier($actor)]);
                $this->audit($account, $actor, 'account.archived', []);
            }

            return $account;
        }, 3);
    }

    private function validate(array $input, ?CompanyCorporateAccount $existing = null): array
    {
        $currency = Rule::exists('currency', 'code');
        // Disabling a currency must not prevent servicing an accepted billing agreement.
        if (!$existing || ($input['currency_code'] ?? null) !== $existing->currency_code) {
            $currency->where('status', 'Active');
        }
        $data = Validator::make($input, [
            'account_name' => 'required|string|max:191', 'legal_name' => 'required|string|max:191',
            'billing_address' => 'required|string|max:2000', 'contact_name' => 'nullable|string|max:191',
            'contact_email' => 'required|email|max:191', 'contact_phone' => 'nullable|string|max:50',
            'currency_code' => ['required', 'string', $currency],
            'billing_timezone' => ['required', Rule::in(\DateTimeZone::listIdentifiers())],
            'billing_cycle' => ['required', Rule::in(['weekly', 'monthly', 'quarterly'])],
            'payment_terms_days' => 'required|integer|min:0|max:365', 'credit_limit' => 'nullable|regex:/^[0-9]{1,10}(\.[0-9]{1,2})?$/',
            'status' => ['required', Rule::in(['Active', 'Inactive'])], 'notes' => 'nullable|string|max:1000',
        ])->validate();
        $data['credit_limit'] = CorporateMoney::decimal(CorporateMoney::minor((string) ($data['credit_limit'] ?? '0')));

        return $data;
    }

    private function audit(CompanyCorporateAccount $account, Admin|Company $actor, string $action, array $data): void
    {
        CompanyCorporateAudit::create(['corporate_account_id' => $account->id, 'action' => $action, 'actor' => $this->authorization->identifier($actor),
            'reference' => 'account:'.$account->id, 'data' => $data, 'created_at' => now('UTC')]);
    }
}
