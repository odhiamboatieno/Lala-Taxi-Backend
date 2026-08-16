@extends('admin.template')

@section('title-sub', 'Add Company Vehicle Type')
@section('pagetitle', 'Add Company Vehicle Type')

@section('css')
<link rel="stylesheet" href="assets/libs/choices.js/public/assets/styles/choices.min.css">
@endsection

@section('main')
<div id="layout-wrapper">
    @include('saasdispatch::partials.company_tabs')
    <div class="row">
        <div class="col-xl-6">
            <div class="card card-h-100">
                <div class="card-body">
                    {!! Form::open(['url' => $isAdmin ? route('saasdispatch.companies.vehicle-types.store', $company) : route('saasdispatch.self.vehicle-types.store'), 'class' => 'row g-5', 'files' => true]) !!}
                    <div class="col-12">
                        <h6 class="mb-3 text-primary">Vehicle Type Details</h6>
                        <p class="text-danger mb-3">(*) Fields are mandatory</p>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Global Category</label>
                        <select name="global_vehicle_category_id" class="form-select">
                            <option value="">Select</option>
                            @foreach($globalVehicleCategories as $category)
                                <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Vehicle Name <em class="text-danger">*</em></label>
                        {!! Form::text('name', old('name'), ['class' => 'form-control']) !!}
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Slug <em class="text-danger">*</em></label>
                        {!! Form::text('slug', old('slug'), ['class' => 'form-control']) !!}
                        <span class="text-danger">{{ $errors->first('slug') }}</span>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        {!! Form::textarea('description', old('description'), ['class' => 'form-control', 'rows' => 3]) !!}
                    </div>
                    <div class="col-12">
                        <label class="form-label">Is Pool <em class="text-danger">*</em></label>
                        {!! Form::select('is_pool', ['No' => 'No', 'Yes' => 'Yes'], old('is_pool', 'No'), ['class' => 'form-select']) !!}
                        <span class="text-danger">{{ $errors->first('is_pool') }}</span>
                    </div>
                    <div class="col-6">
                        <label class="form-label">Passenger Capacity</label>
                        {!! Form::number('passenger_capacity', old('passenger_capacity', 4), ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-6">
                        <label class="form-label">Luggage Capacity</label>
                        {!! Form::number('luggage_capacity', old('luggage_capacity', 0), ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-12">
                        <label class="form-label">Status <em class="text-danger">*</em></label>
                        {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status'), ['class' => 'form-select']) !!}
                        <span class="text-danger">{{ $errors->first('status') }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card">
                <div class="card-body">
                    <div class="col-12">
                        <label class="form-label">Vehicle Image <em class="text-danger">*</em></label>
                        {!! Form::file('vehicle_image', ['class' => 'form-control', 'accept' => 'image/*']) !!}
                        <span class="text-danger">{{ $errors->first('vehicle_image') }}</span>
                    </div>
                    <div class="col-12 mt-4">
                        <label class="form-label">Active Image <em class="text-danger">*</em></label>
                        {!! Form::file('active_image', ['class' => 'form-control', 'accept' => 'image/*']) !!}
                        <span class="text-danger">{{ $errors->first('active_image') }}</span>
                    </div>
                    <div class="col-12 mt-4">
                        <label class="form-label">App Icon</label>
                        {!! Form::file('app_image', ['class' => 'form-control', 'accept' => 'image/*']) !!}
                        <span class="text-danger">{{ $errors->first('app_image') }}</span>
                    </div>
                    <div class="col-12 mt-4">
                        <label class="form-label">Status Icon Path</label>
                        {!! Form::text('icon', old('icon'), ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-12 text-end mt-4">
                        <a href="{{ $isAdmin ? route('saasdispatch.companies.vehicle-types.index', $company) : route('saasdispatch.self.vehicle-types.index') }}" class="btn btn-light-primary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
