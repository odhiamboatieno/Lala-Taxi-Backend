@extends('admin.template')

@section('title-sub', $isEdit ? __('messages.admin.manage_fares.edit_manage_fare') : __('messages.admin.manage_fares.add_manage_fare'))
@section('pagetitle', $isEdit ? __('messages.admin.manage_fares.edit_manage_fare') : __('messages.admin.manage_fares.add_manage_fare'))

@section('main')
@php
    $timeOptionsJson = json_encode($time_options);
    $dayOptionsJson = json_encode($day_options);
@endphp
<div id="layout-wrapper" ng-controller="zoneFareSetup" ng-cloak ng-init="timeOptions={{ $timeOptionsJson }}; dayOptions={{ $dayOptionsJson }};">
    <form method="POST" action="{{ $formAction }}">
        @csrf

        <input type="hidden" name="location_id" value="{{ $location->id }}">

        <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center mb-4 gap-3">
            <div>
                <h3 class="mb-1">Trip Fare Setup - <span class="text-primary">{{ $location->name }}</span></h3>
                <p class="text-muted mb-0">Configure fare setup for all vehicle types in this zone from one screen.</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ $backUrl }}" class="btn btn-light">Back</a>
                <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update Fare Setup' : 'Create Fare Setup' }}</button>
            </div>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Available Vehicles In This Zone</h5>
            </div>
            <div class="card-body">
                <div class="d-flex flex-wrap gap-4">
                    @foreach($vehicleRows as $vehicleRow)
                        <span class="d-inline-flex align-items-center gap-2">
                            <i class="fa fa-check-square text-primary"></i>
                            {{ $vehicleRow['vehicle_name'] }}
                        </span>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="card-title mb-1">Category Wise Trip Fee</h5>
                    <p class="text-muted mb-0">Set fares for all vehicle types under this zone on one screen.</p>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th style="min-width: 220px;">Fare</th>
                                @foreach($vehicleRows as $vehicleRow)
                                    <th class="text-center" style="min-width: 200px;">{{ $vehicleRow['vehicle_name'] }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $fieldRows = [
                                    'base_fare' => 'Base Fare',
                                    'per_km' => 'Fare Per Km',
                                    'min_fare' => 'Minimum Fare',
                                    'per_min' => 'Fare Per Min',
                                    'waiting_time' => 'Free Waiting Time',
                                    'waiting_charge' => 'Waiting Charge',
                                    'schedule_fare' => 'Schedule Fare',
                                    'capacity' => 'Capacity',
                                ];
                            @endphp
                            @foreach($fieldRows as $fieldKey => $fieldLabel)
                                <tr>
                                    <td class="fw-semibold">{{ $fieldLabel }}</td>
                                    @foreach($vehicleRows as $vehicleRow)
                                        <td>
                                            <input
                                                type="text"
                                                class="form-control"
                                                name="fares[{{ $vehicleRow['vehicle_id'] }}][{{ $fieldKey }}]"
                                                value="{{ $vehicleRow[$fieldKey] }}"
                                            >
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            <tr>
                                <td class="fw-semibold">Currency</td>
                                @foreach($vehicleRows as $vehicleRow)
                                    <td>
                                        <select class="form-select" name="fares[{{ $vehicleRow['vehicle_id'] }}][currency_code]">
                                            @foreach($currencyOptions as $code => $label)
                                                <option value="{{ $code }}" {{ $vehicleRow['currency_code'] == $code ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="fw-semibold">Apply Peak Fare</td>
                                @foreach($vehicleRows as $vehicleRow)
                                    <td>
                                        <select class="form-select" name="fares[{{ $vehicleRow['vehicle_id'] }}][apply_peak]" ng-model="fares.{{ $vehicleRow['vehicle_id'] }}.apply_peak">
                                            <option value="Yes" {{ $vehicleRow['apply_peak'] === 'Yes' ? 'selected' : '' }}>Yes</option>
                                            <option value="No" {{ $vehicleRow['apply_peak'] !== 'Yes' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="fw-semibold">Apply Night Fare</td>
                                @foreach($vehicleRows as $vehicleRow)
                                    <td>
                                        <select class="form-select" name="fares[{{ $vehicleRow['vehicle_id'] }}][apply_night]" ng-model="fares.{{ $vehicleRow['vehicle_id'] }}.apply_night">
                                            <option value="Yes" {{ $vehicleRow['apply_night'] === 'Yes' ? 'selected' : '' }}>Yes</option>
                                            <option value="No" {{ $vehicleRow['apply_night'] !== 'Yes' ? 'selected' : '' }}>No</option>
                                        </select>
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($vehicleRows as $vehicleRow)
                <div class="col-12 mb-4" ng-init="fares.{{ $vehicleRow['vehicle_id'] }} = {
                    apply_peak: '{{ $vehicleRow['apply_peak'] }}',
                    apply_night: '{{ $vehicleRow['apply_night'] }}',
                    peak_fare_details: {{ json_encode(array_values($vehicleRow['peak_fare_details'])) }},
                    night_fare_details: {{ json_encode($vehicleRow['night_fare_details']) }}
                }">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">{{ $vehicleRow['vehicle_name'] }} Advanced Pricing</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-4">
                                <div class="col-xl-8" ng-show="fares.{{ $vehicleRow['vehicle_id'] }}.apply_peak == 'Yes'">
                                    <div class="card border shadow-none mb-0">
                                        <div class="card-header">
                                            <h6 class="mb-0">Peak Fare Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3 mb-3" ng-repeat="fareDetail in fares.{{ $vehicleRow['vehicle_id'] }}.peak_fare_details track by $index">
                                                <div class="col-md-3">
                                                    <label class="form-label small">Day</label>
                                                    <select class="form-select form-select-sm" name="fares[{{ $vehicleRow['vehicle_id'] }}][peak_fare_details][@{{$index}}][day]" ng-model="fareDetail.day">
                                                        <option value="">Select</option>
                                                        <option ng-repeat="(key, value) in dayOptions" value="@{{ key }}">@{{ value }}</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">Start Time</label>
                                                    <select class="form-select form-select-sm" name="fares[{{ $vehicleRow['vehicle_id'] }}][peak_fare_details][@{{$index}}][start_time]" ng-model="fareDetail.start_time">
                                                        <option value="">Select</option>
                                                        <option ng-repeat="(key, value) in timeOptions" value="@{{ key }}">@{{ value }}</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label small">End Time</label>
                                                    <select class="form-select form-select-sm" name="fares[{{ $vehicleRow['vehicle_id'] }}][peak_fare_details][@{{$index}}][end_time]" ng-model="fareDetail.end_time">
                                                        <option value="">Select</option>
                                                        <option ng-repeat="(key, value) in timeOptions" value="@{{ key }}">@{{ value }}</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label small">Multiplier</label>
                                                    <input type="text" class="form-control form-control-sm" name="fares[{{ $vehicleRow['vehicle_id'] }}][peak_fare_details][@{{$index}}][price]" ng-model="fareDetail.price">
                                                </div>
                                                <div class="col-md-1 d-flex align-items-end">
                                                    <button type="button" class="btn btn-danger btn-sm" ng-click="removePeakRule({{ $vehicleRow['vehicle_id'] }}, $index)" ng-if="fares.{{ $vehicleRow['vehicle_id'] }}.peak_fare_details.length > 1">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            <button type="button" class="btn btn-outline-primary btn-sm" ng-click="addPeakRule({{ $vehicleRow['vehicle_id'] }})">
                                                <i class="fa fa-plus me-1"></i>Add Peak Fare
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xl-4" ng-show="fares.{{ $vehicleRow['vehicle_id'] }}.apply_night == 'Yes'">
                                    <div class="card border shadow-none mb-0">
                                        <div class="card-header">
                                            <h6 class="mb-0">Night Fare Details</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <label class="form-label small">Start Time</label>
                                                <select class="form-select form-select-sm" name="fares[{{ $vehicleRow['vehicle_id'] }}][night_fare_details][start_time]" ng-model="fares.{{ $vehicleRow['vehicle_id'] }}.night_fare_details.start_time">
                                                    <option value="">Select</option>
                                                    <option ng-repeat="(key, value) in timeOptions" value="@{{ key }}">@{{ value }}</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small">End Time</label>
                                                <select class="form-select form-select-sm" name="fares[{{ $vehicleRow['vehicle_id'] }}][night_fare_details][end_time]" ng-model="fares.{{ $vehicleRow['vehicle_id'] }}.night_fare_details.end_time">
                                                    <option value="">Select</option>
                                                    <option ng-repeat="(key, value) in timeOptions" value="@{{ key }}">@{{ value }}</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="form-label small">Multiplier</label>
                                                <input type="text" class="form-control form-control-sm" name="fares[{{ $vehicleRow['vehicle_id'] }}][night_fare_details][price]" ng-model="fares.{{ $vehicleRow['vehicle_id'] }}.night_fare_details.price">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-end gap-2">
            <a href="{{ $backUrl }}" class="btn btn-light">Reset</a>
            <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Submit Update' : 'Submit' }}</button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
    app.controller('zoneFareSetup', ['$scope', function ($scope) {
        $scope.fares = {};
        $scope.timeOptions = {};
        $scope.dayOptions = {};

        $scope.addPeakRule = function (vehicleId) {
            $scope.fares[vehicleId].peak_fare_details.push({
                day: '',
                start_time: '',
                end_time: '',
                price: ''
            });
        };

        $scope.removePeakRule = function (vehicleId, index) {
            $scope.fares[vehicleId].peak_fare_details.splice(index, 1);
        };
    }]);
</script>
@endpush
