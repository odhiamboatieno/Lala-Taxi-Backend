<?php

namespace Modules\SurgePricing\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Modules\SurgePricing\Entities\SurgePricing;

class SurgePricingResolver
{
    public function resolve(?int $locationId, ?Carbon $moment = null, ?int $companyId = null, string $mode = 'ride', ?int $vehicleTypeId = null): array
    {
        if (!Schema::hasTable('surge_pricings') || !Schema::hasTable('surge_pricing_slots')) {
            return $this->empty();
        }

        $moment = $moment ?: now();
        $date = $moment->toDateString();
        $time = $moment->format('H:i:s');

        $query = SurgePricing::query()
            ->with(['slots', 'locations'])
            ->where('status', true)
            ->where(function ($builder) use ($mode) {
                $builder->where('applies_to', $mode)
                    ->orWhere('applies_to', 'both');
            })
            ->whereHas('slots', function ($slotQuery) use ($date, $time) {
                $slotQuery->whereDate('slot_date', $date)
                    ->where('start_time', '<=', $time)
                    ->where('end_time', '>=', $time);
            });

        if ($locationId) {
            $query->where(function ($builder) use ($locationId) {
                $builder->where('zone_scope', 'all')
                    ->orWhereHas('locations', function ($locationQuery) use ($locationId) {
                        $locationQuery->where('locations.id', $locationId);
                    });
            });
        }

        if ($companyId) {
            $query->where(function ($builder) use ($companyId) {
                $builder->where('company_id', $companyId)
                    ->orWhereNull('company_id');
            })->orderByRaw('CASE WHEN company_id IS NULL THEN 1 ELSE 0 END');
        } else {
            $query->whereNull('company_id');
        }

        $pricing = $query->orderByDesc('id')->first();
        if (!$pricing) {
            return $this->empty();
        }

        $percentage = $mode === 'parcel'
            ? (float) $pricing->parcel_rate
            : (float) $pricing->ride_rate;

        if ($mode === 'ride' && $pricing->ride_rate_mode === 'different' && $vehicleTypeId) {
            $percentage = (float) (($pricing->ride_rate_by_vehicle ?? [])[$vehicleTypeId] ?? $percentage);
        }

        if ($percentage <= 0) {
            return $this->empty();
        }

        return [
            'enabled' => true,
            'surge_pricing_id' => $pricing->id,
            'name' => $pricing->name,
            'percentage' => round($percentage, 2),
            'reason' => trim((string) $pricing->customer_note) ?: $pricing->name,
            'company_id' => $pricing->company_id,
        ];
    }

    public function mergeWithExtraFare(array $baseExtraFare, array $surge): array
    {
        $enabled = ($baseExtraFare['enabled'] ?? false) || ($surge['enabled'] ?? false);
        $percentage = round((float) ($baseExtraFare['percentage'] ?? 0) + (float) ($surge['percentage'] ?? 0), 2);
        $reasons = array_filter([
            $baseExtraFare['enabled'] ?? false ? ($baseExtraFare['reason'] ?? null) : null,
            $surge['enabled'] ?? false ? ($surge['reason'] ?? null) : null,
        ]);

        return [
            'enabled' => $enabled && $percentage > 0,
            'percentage' => $percentage,
            'reason' => implode(' + ', $reasons),
            'surge' => $surge,
        ];
    }

    protected function empty(): array
    {
        return [
            'enabled' => false,
            'surge_pricing_id' => null,
            'name' => null,
            'percentage' => 0,
            'reason' => null,
            'company_id' => null,
        ];
    }
}
