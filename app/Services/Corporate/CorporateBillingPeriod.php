<?php

namespace App\Services\Corporate;

use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;

final class CorporateBillingPeriod
{
    public function containing(CompanyCorporateAccount $account, \DateTimeInterface|string $instant): array
    {
        $date = CarbonImmutable::parse($instant, 'UTC')->setTimezone($this->timezone($account));
        $start = match ($account->billing_cycle) {
            'weekly' => $date->startOfWeek(CarbonImmutable::MONDAY),
            'monthly' => $date->startOfMonth(),
            'quarterly' => $date->startOfQuarter(),
            default => $this->invalid(),
        };

        return $this->bounds($account, $start);
    }

    public function requested(CompanyCorporateAccount $account, string $date): array
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/D', $date)) {
            $this->invalid();
        }
        try {
            $start = CarbonImmutable::createFromFormat('!Y-m-d', $date, $this->timezone($account));
        } catch (\Exception $e) {
            $this->invalid();
        }
        if (!$start || $start->format('Y-m-d') !== $date) {
            $this->invalid();
        }
        $bounds = $this->containing($account, $start);
        if (!$bounds['start']->equalTo($start)) {
            $this->invalid();
        }

        return $bounds;
    }

    private function bounds(CompanyCorporateAccount $account, CarbonImmutable $start): array
    {
        $end = match ($account->billing_cycle) {
            'weekly' => $start->addWeek(),
            'monthly' => $start->addMonth(),
            'quarterly' => $start->addMonths(3),
            default => $this->invalid(),
        };

        return ['start' => $start->utc(), 'end' => $end->utc(), 'timezone' => $this->timezone($account)];
    }

    private function timezone(CompanyCorporateAccount $account): string
    {
        if (!in_array($account->billing_timezone, \DateTimeZone::listIdentifiers(), true)) {
            $this->invalid();
        }

        return $account->billing_timezone;
    }

    private function invalid(): never
    {
        throw ValidationException::withMessages(['period_start' => __('corporate.invalid_period')]);
    }
}
