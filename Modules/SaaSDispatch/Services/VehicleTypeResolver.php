<?php

namespace Modules\SaaSDispatch\Services;

use Modules\SaaSDispatch\Entities\CompanyVehicleCategoryMapping;
use Modules\SaaSDispatch\Entities\CompanyVehicleType;

class VehicleTypeResolver
{
    public function resolve(array $operatorContext, array $payload = []): array
    {
        $globalVehicleTypeId = $payload['global_vehicle_type_id'] ?? $payload['vehicle_type_id'] ?? $payload['car_id'] ?? null;
        $companyVehicleTypeId = $payload['company_vehicle_type_id'] ?? null;
        $globalVehicleCategoryId = $payload['global_vehicle_category_id'] ?? null;

        $companyVehicleType = null;
        if ($companyVehicleTypeId) {
            $companyVehicleType = CompanyVehicleType::query()->find($companyVehicleTypeId);
            $globalVehicleCategoryId = $globalVehicleCategoryId ?: $companyVehicleType?->global_vehicle_category_id;
        }

        if (!$globalVehicleCategoryId && $companyVehicleTypeId) {
            $mapping = CompanyVehicleCategoryMapping::query()
                ->active()
                ->where('company_id', $operatorContext['company_id'])
                ->where('company_vehicle_type_id', $companyVehicleTypeId)
                ->orderBy('priority')
                ->first();

            $globalVehicleCategoryId = $mapping?->global_vehicle_category_id;
        }

        return [
            'car_id' => $globalVehicleTypeId ? (int) $globalVehicleTypeId : null,
            'vehicle_type_id' => $globalVehicleTypeId ? (int) $globalVehicleTypeId : null,
            'global_vehicle_type_id' => $globalVehicleTypeId ? (int) $globalVehicleTypeId : null,
            'company_vehicle_type_id' => $companyVehicleTypeId ? (int) $companyVehicleTypeId : null,
            'global_vehicle_category_id' => $globalVehicleCategoryId ? (int) $globalVehicleCategoryId : null,
            'company_vehicle_type' => $companyVehicleType,
        ];
    }
}
