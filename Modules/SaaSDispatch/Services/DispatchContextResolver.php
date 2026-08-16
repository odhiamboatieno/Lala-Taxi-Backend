<?php

namespace Modules\SaaSDispatch\Services;

use App\Models\User;

class DispatchContextResolver
{
    public function __construct(
        protected OperatorResolver $operatorResolver,
        protected VehicleTypeResolver $vehicleTypeResolver,
        protected ServiceAreaResolver $serviceAreaResolver,
        protected FareResolver $fareResolver,
        protected FeeResolver $feeResolver,
    ) {
    }

    public function forPayload(?User $rider, array $payload): array
    {
        $operatorContext = $this->operatorResolver->resolveForPayload($rider, $payload);
        $vehicleContext = $this->vehicleTypeResolver->resolve($operatorContext, $payload);
        $serviceAreaContext = $this->serviceAreaResolver->resolve($operatorContext, $payload);
        $fareContext = $this->fareResolver->resolve($operatorContext, $vehicleContext, $serviceAreaContext);

        return array_merge($operatorContext, $vehicleContext, [
            'location_id' => $serviceAreaContext['location_id'] ?? null,
            'company_service_area_id' => $serviceAreaContext['company_service_area_id'] ?? null,
            'service_area_source' => $serviceAreaContext['source'] ?? 'global',
            'fare_source' => $fareContext['source'] ?? null,
            'fare_model' => $fareContext['model'] ?? null,
            'fee_items' => $this->feeResolver->resolve($operatorContext),
        ]);
    }
}
