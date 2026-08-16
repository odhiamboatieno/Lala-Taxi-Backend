@extends('admin.template')

@section('main')
<div class="content-wrapper">
    <section class="content-header"><h1>Surge Price Details</h1></section>
    <section class="content">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <div>
                        <h3 class="mb-1">{{ $surgePricing->name }}</h3>
                        <p class="text-muted mb-0">ID #{{ $surgePricing->id }}</p>
                    </div>
                    <div>
                        <a href="{{ url($baseUrl.'/'.$surgePricing->id.'/edit') }}" class="btn btn-info">Edit</a>
                    </div>
                </div>
                <div class="row g-4">
                    <div class="col-md-6"><div class="border rounded p-3 h-100"><h5>Price Increase Rate</h5><p class="mb-1">Ride: {{ $surgePricing->ride_rate }}%</p><p class="mb-0">Parcel: {{ $surgePricing->parcel_rate }}%</p></div></div>
                    <div class="col-md-6"><div class="border rounded p-3 h-100"><h5>Applicable Zones</h5><p class="mb-0">{{ $surgePricing->zone_scope === 'all' ? 'All Zones' : $surgePricing->locations->pluck('name')->implode(', ') }}</p></div></div>
                    <div class="col-md-6"><div class="border rounded p-3 h-100"><h5>Schedule</h5><p class="mb-1">{{ ucfirst($surgePricing->schedule_type) }}</p><p class="mb-0">{{ optional($surgePricing->range_start)->format('d M Y') }} - {{ optional($surgePricing->range_end)->format('d M Y') }}</p></div></div>
                    <div class="col-md-6"><div class="border rounded p-3 h-100"><h5>Customer Note</h5><p class="mb-0">{{ $surgePricing->customer_note ?: 'Not provided' }}</p></div></div>
                    <div class="col-12"><div class="border rounded p-3"><h5>Scheduled Slots</h5><ul class="mb-0">@forelse($surgePricing->slots as $slot)<li>{{ optional($slot->slot_date)->format('d M Y') }}: {{ substr($slot->start_time, 0, 5) }} - {{ substr($slot->end_time, 0, 5) }}</li>@empty<li>No slots configured.</li>@endforelse</ul></div></div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
