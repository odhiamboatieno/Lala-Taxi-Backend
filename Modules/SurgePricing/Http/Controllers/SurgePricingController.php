<?php

namespace Modules\SurgePricing\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Modules\SurgePricing\Entities\SurgePricing;
use Modules\SurgePricing\Entities\SurgePricingSlot;
use Modules\SurgePricing\Http\Requests\SurgePricingRequest;

class SurgePricingController extends Controller
{
    public function index(Request $request)
    {
        $companyId = LOGIN_USER_TYPE === 'company' ? Auth::guard('company')->id() : null;
        $baseUrl = LOGIN_USER_TYPE === 'company' ? 'company/surge-pricing' : 'admin/surge-pricing';

        $query = SurgePricing::query()->with('locations');
        $query->when($companyId, fn ($builder) => $builder->where('company_id', $companyId), fn ($builder) => $builder->whereNull('company_id'));

        if ($search = trim((string) $request->get('search'))) {
            $query->where('name', 'like', '%'.$search.'%');
        }

        $surgePricings = $query->orderByDesc('id')->paginate(15)->withQueryString();

        return view('surgepricing::surge_pricings.index', compact('surgePricings', 'baseUrl', 'search'));
    }

    public function create()
    {
        return view('surgepricing::surge_pricings.form', [
            'surgePricing' => new SurgePricing(),
            'locations' => Location::query()->where('status', 'Active')->orderBy('name')->get(),
            'selectedLocationIds' => [],
            'slots' => [],
            'baseUrl' => LOGIN_USER_TYPE === 'company' ? 'company/surge-pricing' : 'admin/surge-pricing',
        ]);
    }

    public function store(SurgePricingRequest $request)
    {
        $surgePricing = new SurgePricing();
        $this->persist($surgePricing, $request);

        flashMessage('success', 'Surge pricing created successfully');

        return redirect((LOGIN_USER_TYPE === 'company' ? 'company' : 'admin').'/surge-pricing');
    }

    public function show(SurgePricing $surgePricing)
    {
        $this->authorizeContext($surgePricing);
        $surgePricing->load('locations', 'slots');

        return view('surgepricing::surge_pricings.show', [
            'surgePricing' => $surgePricing,
            'baseUrl' => LOGIN_USER_TYPE === 'company' ? 'company/surge-pricing' : 'admin/surge-pricing',
        ]);
    }

    public function edit(SurgePricing $surgePricing)
    {
        $this->authorizeContext($surgePricing);
        $surgePricing->load('locations', 'slots');

        return view('surgepricing::surge_pricings.form', [
            'surgePricing' => $surgePricing,
            'locations' => Location::query()->where('status', 'Active')->orderBy('name')->get(),
            'selectedLocationIds' => $surgePricing->locations->pluck('id')->all(),
            'slots' => $surgePricing->slots->map(fn ($slot) => ['date' => optional($slot->slot_date)->format('Y-m-d'), 'start_time' => $slot->start_time, 'end_time' => $slot->end_time])->all(),
            'baseUrl' => LOGIN_USER_TYPE === 'company' ? 'company/surge-pricing' : 'admin/surge-pricing',
        ]);
    }

    public function update(SurgePricingRequest $request, SurgePricing $surgePricing)
    {
        $this->authorizeContext($surgePricing);
        $this->persist($surgePricing, $request);

        flashMessage('success', 'Surge pricing updated successfully');

        return redirect((LOGIN_USER_TYPE === 'company' ? 'company' : 'admin').'/surge-pricing');
    }

    public function destroy(SurgePricing $surgePricing)
    {
        $this->authorizeContext($surgePricing);
        $surgePricing->locations()->detach();
        $surgePricing->slots()->delete();
        $surgePricing->delete();

        flashMessage('success', 'Surge pricing deleted successfully');

        return back();
    }

    public function toggle(SurgePricing $surgePricing)
    {
        $this->authorizeContext($surgePricing);
        $surgePricing->status = !$surgePricing->status;
        $surgePricing->save();

        flashMessage('success', 'Surge pricing status updated');

        return back();
    }

    protected function persist(SurgePricing $surgePricing, SurgePricingRequest $request): void
    {
        $companyId = LOGIN_USER_TYPE === 'company' ? Auth::guard('company')->id() : null;

        $surgePricing->company_id = $companyId;
        $surgePricing->name = $request->name;
        $surgePricing->applies_to = $request->applies_to;
        $surgePricing->zone_scope = $request->zone_scope;
        $surgePricing->ride_rate_mode = $request->ride_rate_mode;
        $surgePricing->ride_rate = $request->ride_rate ?: 0;
        $surgePricing->parcel_rate = $request->parcel_rate ?: 0;
        $surgePricing->schedule_type = $request->schedule_type;
        $surgePricing->range_start = $request->range_start;
        $surgePricing->range_end = $request->range_end;
        $surgePricing->weekdays = $request->weekdays ?: [];
        $surgePricing->customer_note = $request->customer_note;
        $surgePricing->status = $request->boolean('status', true);
        $surgePricing->save();

        $surgePricing->locations()->sync($request->zone_scope === 'selected' ? ($request->location_ids ?: []) : []);

        $surgePricing->slots()->delete();
        foreach ($request->time_ranges ?: [] as $timeRange) {
            if (empty($timeRange['date']) || empty($timeRange['start_time']) || empty($timeRange['end_time'])) {
                continue;
            }

            SurgePricingSlot::query()->create([
                'surge_pricing_id' => $surgePricing->id,
                'slot_date' => Carbon::parse($timeRange['date'])->format('Y-m-d'),
                'start_time' => Carbon::parse($timeRange['start_time'])->format('H:i:s'),
                'end_time' => Carbon::parse($timeRange['end_time'])->format('H:i:s'),
            ]);
        }
    }

    protected function authorizeContext(SurgePricing $surgePricing): void
    {
        if (LOGIN_USER_TYPE === 'company') {
            abort_unless($surgePricing->company_id === Auth::guard('company')->id(), 404);
            return;
        }

        abort_unless($surgePricing->company_id === null, 404);
    }
}
