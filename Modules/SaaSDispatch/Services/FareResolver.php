<?php

namespace Modules\SaaSDispatch\Services;

use App\Models\ManageFare;
use Modules\SaaSDispatch\Entities\CompanyFareRule;

class FareResolver
{
    public function resolve(array $operatorContext, array $vehicleContext, array $serviceAreaContext): ?array
    {
        $globalFare = $this->resolveGlobalFare($serviceAreaContext['location_id'] ?? null, $vehicleContext['global_vehicle_type_id'] ?? null);

        if ($operatorContext['is_platform'] || !data_get($operatorContext, 'company_config.use_own_fares')) {
            return $globalFare;
        }

        $companyFare = null;
        if (!empty($vehicleContext['company_vehicle_type_id'])) {
            $companyFare = CompanyFareRule::query()
                ->active()
                ->where('company_id', $operatorContext['company_id'])
                ->where('company_vehicle_type_id', $vehicleContext['company_vehicle_type_id'])
                ->when(
                    !empty($serviceAreaContext['company_service_area_id']),
                    fn ($query) => $query->where('company_service_area_id', $serviceAreaContext['company_service_area_id']),
                    fn ($query) => $query->whereNull('company_service_area_id')
                )
                ->first();
        }

        if ($companyFare) {
            return [
                'source' => 'company',
                'model' => $companyFare,
            ];
        }

        if (data_get($operatorContext, 'dispatch_mode') === 'hybrid') {
            return $globalFare;
        }

        return [
            'source' => 'company_missing',
            'model' => null,
        ];
    }

    protected function resolveGlobalFare($locationId, $vehicleTypeId): ?array
    {
        if (!$locationId || !$vehicleTypeId) {
            return null;
        }

        $fare = ManageFare::query()
            ->where('location_id', $locationId)
            ->where('vehicle_id', $vehicleTypeId)
            ->first();

        return [
            'source' => 'global',
            'model' => $fare,
        ];
    }
}
