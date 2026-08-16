<?php

namespace Modules\SaaSDispatch\Services;

use Modules\SaaSDispatch\Entities\CompanyServiceArea;

class ServiceAreaResolver
{
    public function resolve(array $operatorContext, array $payload = []): array
    {
        $locationId = isset($payload['location_id']) ? (int) $payload['location_id'] : null;
        $companyServiceAreaId = $payload['company_service_area_id'] ?? null;

        if ($operatorContext['is_platform'] || !data_get($operatorContext, 'company_config.use_own_locations')) {
            return [
                'source' => 'global',
                'location_id' => $locationId,
                'company_service_area_id' => null,
            ];
        }

        $serviceArea = null;
        if ($companyServiceAreaId) {
            $serviceArea = CompanyServiceArea::query()
                ->active()
                ->where('company_id', $operatorContext['company_id'])
                ->find($companyServiceAreaId);
        }

        if (!$serviceArea && data_get($operatorContext, 'dispatch_mode') === 'hybrid') {
            return [
                'source' => 'global',
                'location_id' => $locationId,
                'company_service_area_id' => null,
            ];
        }

        return [
            'source' => $serviceArea ? 'company' : 'company_missing',
            'location_id' => $locationId,
            'company_service_area_id' => $serviceArea?->id,
            'service_area' => $serviceArea,
        ];
    }
}
