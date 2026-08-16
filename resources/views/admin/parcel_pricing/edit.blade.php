@extends('admin.template')

@section('title-sub', 'Edit Parcel Pricing')
@section('pagetitle', 'Edit Parcel Pricing')

@section('main')
<div id="layout-wrapper">
    <div class="card"><div class="card-header"><h4 class="mb-1">Edit Parcel Pricing Rule</h4></div><div class="card-body">
        <form method="POST" action="{{ url('admin/edit_parcel_pricing/'.$result->id) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-4"><label class="form-label">Category</label><select name="parcel_category_id" class="form-select">@foreach($categories as $category)<option value="{{ $category->id }}" {{ (int) old('parcel_category_id', $result->parcel_category_id) === (int) $category->id ? 'selected' : '' }}>{{ $category->title }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Vehicle Type</label><select name="car_type_id" class="form-select">@foreach($car_types as $carType)<option value="{{ $carType->id }}" {{ (int) old('car_type_id', $result->car_type_id) === (int) $carType->id ? 'selected' : '' }}>{{ $carType->car_name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Zone</label><select name="location_id" class="form-select"><option value="">Default</option>@foreach($locations as $location)<option value="{{ $location->id }}" {{ (int) old('location_id', $result->location_id) === (int) $location->id ? 'selected' : '' }}>{{ $location->name }}</option>@endforeach</select></div>
            <div class="col-md-4"><label class="form-label">Base Fare</label><input name="base_fare" class="form-control" value="{{ old('base_fare', $result->base_fare) }}"></div>
            <div class="col-md-4"><label class="form-label">Per KM Rate</label><input name="per_km_rate" class="form-control" value="{{ old('per_km_rate', $result->per_km_rate) }}"></div>
            <div class="col-md-4"><label class="form-label">Min Fare</label><input name="min_fare" class="form-control" value="{{ old('min_fare', $result->min_fare) }}"></div>
            <div class="col-12"><div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" id="fare_active" {{ old('is_active', $result->is_active) ? 'checked' : '' }}><label class="form-check-label" for="fare_active">Active</label></div></div>
            <div class="col-12 d-flex justify-content-end gap-2"><a href="{{ url('admin/parcel_pricing') }}" class="btn btn-light">Back</a><button type="submit" name="submit" value="submit" class="btn btn-primary">Update</button></div>
        </form>
    </div></div>
</div>
@endsection
