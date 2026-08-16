@extends('admin.template')

@section('title-sub', 'Dispatch Settings')
@section('pagetitle', 'Dispatch Settings')

@section('main')
<div id="layout-wrapper">
    @include('saasdispatch::partials.company_tabs')

    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Dispatch Settings</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ $isAdmin ? route('saasdispatch.companies.dispatch-settings.update', $company) : route('saasdispatch.self.dispatch-settings.update') }}" class="row g-4">
                @csrf
                @method('PUT')

                <div class="col-md-4"><label class="form-label">Dispatch Strategy</label><input class="form-control" name="dispatch_strategy" value="{{ old('dispatch_strategy', $dispatchSetting->dispatch_strategy) }}"></div>
                <div class="col-md-4"><label class="form-label">Driver Search Radius</label><input class="form-control" type="number" step="0.01" name="driver_search_radius" value="{{ old('driver_search_radius', $dispatchSetting->driver_search_radius) }}" required></div>
                <div class="col-md-4"><label class="form-label">Max Search Radius</label><input class="form-control" type="number" step="0.01" name="max_driver_search_radius" value="{{ old('max_driver_search_radius', $dispatchSetting->max_driver_search_radius) }}" required></div>
                <div class="col-md-4"><label class="form-label">Request Timeout Seconds</label><input class="form-control" type="number" name="request_timeout_seconds" value="{{ old('request_timeout_seconds', $dispatchSetting->request_timeout_seconds) }}" required></div>
                <div class="col-md-4"><label class="form-label">Retry Rounds</label><input class="form-control" type="number" name="retry_rounds" value="{{ old('retry_rounds', $dispatchSetting->retry_rounds) }}" required></div>
                <div class="col-md-4">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option {{ old('status', $dispatchSetting->status) === 'Active' ? 'selected' : '' }}>Active</option>
                        <option {{ old('status', $dispatchSetting->status) === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-12">
                    <hr>
                    <div class="row g-3">
                        @foreach([
                            'allow_auto_dispatch' => 'Allow auto dispatch',
                            'allow_manual_dispatch' => 'Allow manual dispatch',
                            'allow_scheduled_trips' => 'Allow scheduled trips',
                            'allow_airport_trips' => 'Allow airport trips',
                            'allow_out_of_area_trips' => 'Allow out-of-area trips',
                        ] as $field => $label)
                            <div class="col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="{{ $field }}" value="1" id="{{ $field }}" {{ old($field, $dispatchSetting->{$field}) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="{{ $field }}">{{ $label }}</label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="col-12"><button class="btn btn-primary">Save Dispatch Settings</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
