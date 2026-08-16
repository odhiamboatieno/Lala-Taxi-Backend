<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\DriverDocuments;
use App\Models\User;
use App\Models\Vehicle;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ChecksCompanyFeatureModule;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanyDocumentsController extends Controller
{
    use ChecksCompanyFeatureModule;
    use ResolvesSaaSCompanyContext;

    public function index(CompanyContextResolver $resolver)
    {
        $this->ensureCompanyFeatureEnabled('company_documents');
        $context = $this->companyContext($resolver);
        $companyId = $context['company']->id;

        $drivers = User::query()
            ->where('company_id', $companyId)
            ->where('user_type', 'Driver')
            ->orderBy('first_name')
            ->get();

        $vehicles = Vehicle::query()
            ->whereHas('driver', fn ($query) => $query->where('company_id', $companyId))
            ->with('driver')
            ->orderByDesc('id')
            ->get();

        $driverDocumentCounts = DriverDocuments::query()
            ->selectRaw('user_id, COUNT(*) as total')
            ->where('type', 'Driver')
            ->whereIn('user_id', $drivers->pluck('id'))
            ->groupBy('user_id')
            ->pluck('total', 'user_id');

        $vehicleDocumentCounts = DriverDocuments::query()
            ->selectRaw('vehicle_id, COUNT(*) as total')
            ->where('type', 'Vehicle')
            ->whereIn('vehicle_id', $vehicles->pluck('id'))
            ->groupBy('vehicle_id')
            ->pluck('total', 'vehicle_id');

        return view('saasdispatch::documents.index', array_merge($context, [
            'drivers' => $drivers,
            'vehicles' => $vehicles,
            'driverDocumentCounts' => $driverDocumentCounts,
            'vehicleDocumentCounts' => $vehicleDocumentCounts,
        ]));
    }
}
