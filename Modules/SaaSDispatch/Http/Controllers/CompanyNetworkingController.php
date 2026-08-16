<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Request as RideRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\SaaSDispatch\Entities\CompanyNetworkPartner;
use Modules\SaaSDispatch\Entities\CompanyNetworkTripShare;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ChecksCompanyFeatureModule;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanyNetworkingController extends Controller
{
    use ChecksCompanyFeatureModule;
    use ResolvesSaaSCompanyContext;

    public function index(CompanyContextResolver $resolver)
    {
        $this->ensureCompanyFeatureEnabled('networking');
        $context = $this->companyContext($resolver);
        $companyId = $context['company']->id;

        return view('saasdispatch::networking.index', array_merge($context, [
            'availableCompanies' => Company::query()->where('id', '<>', $companyId)->orderBy('name')->get(),
            'partners' => CompanyNetworkPartner::query()->where('company_id', $companyId)->with('partnerCompany')->latest('id')->get(),
            'incomingShares' => CompanyNetworkTripShare::query()->where('target_company_id', $companyId)->latest('id')->get(),
            'outgoingShares' => CompanyNetworkTripShare::query()->where('source_company_id', $companyId)->latest('id')->get(),
            'pendingRequests' => RideRequest::query()->where('company_id', $companyId)->latest('id')->limit(20)->get(),
        ]));
    }

    public function storePartner(Request $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $this->ensureCompanyFeatureEnabled('networking');
        $context = $this->companyContext($resolver);

        $request->validate([
            'partner_company_id' => ['required', 'integer', 'different:company_id'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        CompanyNetworkPartner::query()->updateOrCreate(
            [
                'company_id' => $context['company']->id,
                'partner_company_id' => (int) $request->partner_company_id,
            ],
            [
                'status' => 'Active',
                'notes' => $request->notes,
            ]
        );

        flashMessage('success', 'Partner fleet connected successfully.');

        return redirect()->route('saasdispatch.self.networking.index');
    }

    public function share(Request $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $this->ensureCompanyFeatureEnabled('networking');
        $context = $this->companyContext($resolver);
        $companyId = $context['company']->id;

        $request->validate([
            'target_company_id' => ['required', 'integer'],
            'request_id' => ['required', 'integer'],
            'share_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $tripShare = CompanyNetworkTripShare::query()->create([
            'source_company_id' => $companyId,
            'target_company_id' => (int) $request->target_company_id,
            'request_id' => (int) $request->request_id,
            'status' => 'pending',
            'share_note' => $request->share_note,
            'shared_at' => now(),
        ]);

        RideRequest::query()->whereKey($tripShare->request_id)->update([
            'dispatch_mode' => 'network_shared',
        ]);

        flashMessage('success', 'Trip request shared with partner fleet.');

        return redirect()->route('saasdispatch.self.networking.index');
    }

    public function respond(Request $request, CompanyContextResolver $resolver, CompanyNetworkTripShare $share): RedirectResponse
    {
        $this->ensureCompanyFeatureEnabled('networking');
        $context = $this->companyContext($resolver);
        abort_unless((int) $share->target_company_id === (int) $context['company']->id, 404);

        $request->validate([
            'status' => ['required', 'in:accepted,rejected'],
            'response_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $share->update([
            'status' => $request->status,
            'response_note' => $request->response_note,
            'responded_at' => now(),
            'accepted_by_company_id' => $request->status === 'accepted' ? $context['company']->id : null,
        ]);

        if ($request->status === 'accepted' && $share->request_id) {
            RideRequest::query()->whereKey($share->request_id)->update([
                'company_id' => $context['company']->id,
                'dispatch_mode' => 'network_received',
            ]);
        }

        flashMessage('success', 'Trip share response saved.');

        return redirect()->route('saasdispatch.self.networking.index');
    }
}
