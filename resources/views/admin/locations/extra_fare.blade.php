@extends('admin.template')

@section('title-sub', $location->name)
@section('pagetitle', $location->name)

@section('main')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                {!! Form::open(['url' => url('admin/location_extra_fare/'.$location->id)]) !!}
                    <input type="hidden" name="extra_fare_enabled" value="0">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                            <div>
                                <h4 class="mb-2">{{ $location->name }}</h4>
                            </div>
                        </div>
                    </div>

                    <div class="card border shadow-none mx-4 mb-4">
                        <div class="card-body">
                            <div class="d-flex align-items-start justify-content-between flex-wrap gap-3 mb-4">
                                <div>
                                    <h4 class="mb-2">Zone Wise Extra Fare</h4>
                                    <p class="text-muted mb-0">To turn this feature on customer will pay the extra fare for each trip & parcel delivery service.</p>
                                </div>
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" role="switch" name="extra_fare_enabled" value="1" {{ old('extra_fare_enabled', $location->extra_fare_enabled) ? 'checked' : '' }}>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border shadow-none mx-4 mb-4">
                        <div class="card-body">
                            <div class="row g-4 align-items-start">
                                <div class="col-xl-4">
                                    <h4 class="mb-3">Extra Fare Setup</h4>
                                    <p class="text-muted mb-0">Here you can setup why you charge extra fare & set how much price will be increased for the reason.</p>
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label">Extra Fare (%) <i class="bi bi-info-circle text-info"></i></label>
                                    <input type="number" name="extra_fare_percentage" step="0.01" min="0" value="{{ old('extra_fare_percentage', $location->extra_fare_percentage) }}" class="form-control form-control-lg">
                                    <span class="text-danger">{{ $errors->first('extra_fare_percentage') }}</span>
                                </div>
                                <div class="col-xl-4">
                                    <label class="form-label">Reasons For Extra Fare</label>
                                    <input type="text" name="extra_fare_reason" value="{{ old('extra_fare_reason', $location->extra_fare_reason) }}" class="form-control form-control-lg">
                                    <span class="text-danger">{{ $errors->first('extra_fare_reason') }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-3 px-4 pb-4">
                        <a href="{{ url('admin/locations') }}" class="btn btn-light px-4">Reset</a>
                        <button type="submit" class="btn btn-primary px-4">Update</button>
                    </div>
                {!! Form::close() !!}
            </div>
        </div>
    </div>
</div>
@endsection
