@extends('admin.template')

@section('main')
<div class="content-wrapper">
    <section class="content-header"><h1>{{ $surgePricing->exists ? 'Edit Surge Price' : 'Create Surge Price' }}</h1></section>
    <section class="content">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ $surgePricing->exists ? url($baseUrl.'/'.$surgePricing->id) : url($baseUrl) }}">
                    @csrf
                    @if($surgePricing->exists) @method('PUT') @endif
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">Name</label><input type="text" name="name" class="form-control" value="{{ old('name', $surgePricing->name) }}" required></div>
                        <div class="col-md-3"><label class="form-label">Applies To</label><select name="applies_to" class="form-control"><option value="ride" {{ old('applies_to', $surgePricing->applies_to) === 'ride' ? 'selected' : '' }}>Ride</option><option value="parcel" {{ old('applies_to', $surgePricing->applies_to) === 'parcel' ? 'selected' : '' }}>Parcel</option><option value="both" {{ old('applies_to', $surgePricing->applies_to) === 'both' ? 'selected' : '' }}>Both</option></select></div>
                        <div class="col-md-3"><label class="form-label">Zone Scope</label><select name="zone_scope" class="form-control"><option value="all" {{ old('zone_scope', $surgePricing->zone_scope) === 'all' ? 'selected' : '' }}>All zones</option><option value="selected" {{ old('zone_scope', $surgePricing->zone_scope) === 'selected' ? 'selected' : '' }}>Selected zones</option></select></div>
                        <div class="col-md-6"><label class="form-label">Zones</label><select name="location_ids[]" class="form-control" multiple size="6">@foreach($locations as $location)<option value="{{ $location->id }}" {{ in_array($location->id, old('location_ids', $selectedLocationIds), true) ? 'selected' : '' }}>{{ $location->name }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Ride Rate Mode</label><select name="ride_rate_mode" class="form-control"><option value="same" {{ old('ride_rate_mode', $surgePricing->ride_rate_mode) === 'same' ? 'selected' : '' }}>Same for all vehicle</option><option value="different" {{ old('ride_rate_mode', $surgePricing->ride_rate_mode) === 'different' ? 'selected' : '' }}>Different by vehicle</option></select></div>
                        <div class="col-md-3"><label class="form-label">Ride Rate %</label><input type="number" step="0.01" name="ride_rate" class="form-control" value="{{ old('ride_rate', $surgePricing->ride_rate) }}"></div>
                        <div class="col-md-3"><label class="form-label">Parcel Rate %</label><input type="number" step="0.01" name="parcel_rate" class="form-control" value="{{ old('parcel_rate', $surgePricing->parcel_rate) }}"></div>
                        <div class="col-md-3"><label class="form-label">Schedule Type</label><select name="schedule_type" class="form-control"><option value="daily" {{ old('schedule_type', $surgePricing->schedule_type) === 'daily' ? 'selected' : '' }}>Daily</option><option value="weekly" {{ old('schedule_type', $surgePricing->schedule_type) === 'weekly' ? 'selected' : '' }}>Weekly</option><option value="custom" {{ old('schedule_type', $surgePricing->schedule_type) === 'custom' ? 'selected' : '' }}>Custom</option></select></div>
                        <div class="col-md-3"><label class="form-label">Range Start</label><input type="date" name="range_start" class="form-control" value="{{ old('range_start', optional($surgePricing->range_start)->format('Y-m-d')) }}"></div>
                        <div class="col-md-3"><label class="form-label">Range End</label><input type="date" name="range_end" class="form-control" value="{{ old('range_end', optional($surgePricing->range_end)->format('Y-m-d')) }}"></div>
                        <div class="col-md-12"><label class="form-label">Customer Note</label><textarea name="customer_note" class="form-control" rows="3">{{ old('customer_note', $surgePricing->customer_note) }}</textarea></div>
                    </div>
                    <hr>
                    <h5>Time Slots</h5>
                    <p class="text-muted">Add exact dates and time ranges when this surge price should be active.</p>
                    <div id="surge-slots-wrapper">
                        @php $slotRows = old('time_ranges', $slots ?: [['date' => '', 'start_time' => '', 'end_time' => '']]); @endphp
                        @foreach($slotRows as $index => $slot)
                            <div class="row g-3 mb-3">
                                <div class="col-md-4"><input type="date" name="time_ranges[{{ $index }}][date]" class="form-control" value="{{ $slot['date'] ?? '' }}"></div>
                                <div class="col-md-4"><input type="time" name="time_ranges[{{ $index }}][start_time]" class="form-control" value="{{ isset($slot['start_time']) ? substr($slot['start_time'], 0, 5) : '' }}"></div>
                                <div class="col-md-4"><input type="time" name="time_ranges[{{ $index }}][end_time]" class="form-control" value="{{ isset($slot['end_time']) ? substr($slot['end_time'], 0, 5) : '' }}"></div>
                            </div>
                        @endforeach
                    </div>
                    <div class="form-check form-switch my-3">
                        <input type="checkbox" class="form-check-input" name="status" value="1" {{ old('status', $surgePricing->status ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                    <div class="text-end">
                        <a href="{{ url($baseUrl) }}" class="btn btn-secondary">Cancel</a>
                        <button class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
