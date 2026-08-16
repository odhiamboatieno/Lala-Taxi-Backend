<?php

namespace App\Services;

use App\Models\Documents;
use App\Models\DriverDocuments;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DriverComplianceService
{
    public function compliancePayload(User $driver): array
    {
        $driver->loadMissing(['vehicles', 'company', 'profile_picture']);

        $driverDocuments = DriverDocuments::where('user_id', $driver->id)
            ->where('type', 'Driver')
            ->where('vehicle_id', 0)
            ->get();

        $vehicleDocuments = DriverDocuments::where('user_id', $driver->id)
            ->where('type', 'Vehicle')
            ->get();

        $requiredDriverDocs = CheckDocument('Driver', $driver->country_id ?: 'all');
        $requiredVehicleDocs = CheckDocument('Vehicle', $driver->country_id ?: 'all');

        $driverDocIssues = $this->collectDocumentIssues($driverDocuments, $requiredDriverDocs, 'Driver');
        $vehicleDocIssues = $this->collectVehicleIssues($driver, $vehicleDocuments, $requiredVehicleDocs);

        $issues = array_merge($driverDocIssues, $vehicleDocIssues);

        return [
            'is_compliant' => count($issues) === 0,
            'issues' => $issues,
            'driver_documents' => $this->serializeDocuments($driverDocuments),
            'vehicle_documents' => $this->serializeDocuments($vehicleDocuments),
            'profile_image_approval_status' => optional($driver->profile_picture)->approval_status ?: 'approved',
            'profile_image_pending' => !empty(optional($driver->profile_picture)->pending_src),
        ];
    }

    public function canGoOnline(User $driver): bool
    {
        return $this->compliancePayload($driver)['is_compliant'];
    }

    public function syncDocumentExpiryStatuses(): void
    {
        $today = Carbon::today()->toDateString();

        DriverDocuments::query()
            ->whereNotNull('expired_date')
            ->whereDate('expired_date', '<', $today)
            ->where('status', '!=', '2')
            ->update([
                'status' => '2',
                'rejection_reason' => 'Document expired automatically.',
                'reviewed_at' => now(),
            ]);

        if (class_exists(\App\Models\CompanyDocuments::class)) {
            \App\Models\CompanyDocuments::query()
                ->whereNotNull('expired_date')
                ->whereDate('expired_date', '<', $today)
                ->where('status', '!=', '2')
                ->update([
                    'status' => '2',
                    'rejection_reason' => 'Document expired automatically.',
                    'reviewed_at' => now(),
                ]);
        }
    }

    protected function collectDocumentIssues(Collection $documents, Collection $requiredDocuments, string $scope): array
    {
        $issues = [];
        $documentsById = $documents->keyBy('document_id');

        foreach ($requiredDocuments as $required) {
            $document = $documentsById->get($required->id);
            if (!$document || empty($document->document)) {
                $issues[] = $this->buildIssue($scope, $required->document_name, 'missing', 'Document is missing.');
                continue;
            }

            $issues = array_merge($issues, $this->evaluateDocument($document, $required->document_name, $scope));
        }

        return $issues;
    }

    protected function collectVehicleIssues(User $driver, Collection $vehicleDocuments, Collection $requiredVehicleDocuments): array
    {
        $issues = [];
        $documentsByVehicle = $vehicleDocuments->groupBy(function (DriverDocuments $document) {
            return (int) $document->getRawOriginal('vehicle_id');
        });
        $vehiclesToCheck = $this->relevantVehicles($driver);

        foreach ($vehiclesToCheck as $vehicle) {
            $vehicleDocSet = $documentsByVehicle->get($vehicle->id, collect())->keyBy('document_id');
            foreach ($requiredVehicleDocuments as $required) {
                $document = $vehicleDocSet->get($required->id);
                $scope = 'Vehicle: '.($vehicle->vehicle_name ?: 'Vehicle #'.$vehicle->id);

                if (!$document || empty($document->document)) {
                    $issues[] = $this->buildIssue($scope, $required->document_name, 'missing', 'Document is missing.');
                    continue;
                }

                $issues = array_merge($issues, $this->evaluateDocument($document, $required->document_name, $scope));
            }
        }

        if ($vehiclesToCheck->isEmpty()) {
            $issues[] = $this->buildIssue('Vehicle', 'Default vehicle', 'missing', 'No vehicle has been added yet.');
        }

        return $issues;
    }

    protected function relevantVehicles(User $driver): Collection
    {
        $vehicles = $driver->vehicles instanceof Collection ? $driver->vehicles : collect($driver->vehicles);

        if ($vehicles->isEmpty()) {
            return collect();
        }

        $activeVehicles = $vehicles->where('is_active', 1);
        if ($activeVehicles->isNotEmpty()) {
            return $activeVehicles->values();
        }

        $defaultVehicle = $vehicles->firstWhere('default_type', '1') ?: $vehicles->firstWhere('default_type', 1);
        if ($defaultVehicle) {
            return collect([$defaultVehicle]);
        }

        if ($vehicles->count() === 1) {
            return $vehicles->values();
        }

        return collect([$vehicles->sortBy('id')->first()]);
    }

    protected function evaluateDocument(DriverDocuments $document, string $documentName, string $scope): array
    {
        $issues = [];
        $status = (string) $document->status;
        $expiredDate = $document->expired_date ? Carbon::parse($document->expired_date) : null;

        if ($expiredDate && $expiredDate->isPast() && !$expiredDate->isToday()) {
            $issues[] = $this->buildIssue($scope, $documentName, 'expired', 'Document has expired.');
        } elseif ($status === '0') {
            $issues[] = $this->buildIssue($scope, $documentName, 'pending', 'Document is pending approval.');
        } elseif ($status === '2') {
            $issues[] = $this->buildIssue($scope, $documentName, 'rejected', $document->rejection_reason ?: 'Document is not approved.');
        }

        return $issues;
    }

    protected function buildIssue(string $scope, string $documentName, string $code, string $message): array
    {
        return [
            'scope' => $scope,
            'document_name' => $documentName,
            'code' => $code,
            'message' => $message,
        ];
    }

    protected function serializeDocuments(Collection $documents): array
    {
        return $documents->map(function (DriverDocuments $document) {
            return [
                'id' => $document->id,
                'document_id' => $document->document_id,
                'type' => $document->type,
                'vehicle_id' => (int) $document->getRawOriginal('vehicle_id'),
                'status' => (string) $document->status,
                'expired_date' => $document->expired_date,
                'rejection_reason' => $document->rejection_reason,
                'document_name' => $document->document_name,
                'document' => $document->document,
            ];
        })->values()->all();
    }
}
