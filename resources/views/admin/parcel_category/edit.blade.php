@extends('admin.template')

@section('title-sub', 'Edit Parcel Category')
@section('pagetitle', 'Edit Parcel Category')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header"><h4 class="mb-1">Edit Parcel Category</h4></div>
        <div class="card-body">
            <form method="POST" action="{{ url('admin/edit_parcel_category/'.$parcelCategory->id) }}" enctype="multipart/form-data" class="row g-3">
                @csrf
                @method('PUT')
                <div class="col-md-4"><label class="form-label">Key</label><input name="key" class="form-control" value="{{ old('key', $parcelCategory->key) }}"></div>
                <div class="col-md-4"><label class="form-label">Title</label><input name="title" class="form-control" value="{{ old('title', $parcelCategory->title) }}"></div>
                <div class="col-md-4"><label class="form-label">Icon</label><input type="file" name="icon_path" class="form-control"></div>
                <div class="col-md-4"><label class="form-label">Base Fare</label><input name="base_fare" class="form-control" value="{{ old('base_fare', $parcelCategory->base_fare) }}"></div>
                <div class="col-md-4"><label class="form-label">Multiplier</label><input name="multiplier" class="form-control" value="{{ old('multiplier', $parcelCategory->multiplier) }}"></div>
                <div class="col-md-4"><label class="form-label">Sort Order</label><input name="sort_order" class="form-control" value="{{ old('sort_order', $parcelCategory->sort_order) }}"></div>
                <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4">{{ old('description', $parcelCategory->description) }}</textarea></div>
                <div class="col-12"><div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" id="is_active" {{ old('is_active', $parcelCategory->is_active) ? 'checked' : '' }}><label class="form-check-label" for="is_active">Active</label></div></div>
                <div class="col-12 d-flex justify-content-end gap-2"><a href="{{ url('admin/parcel_categories') }}" class="btn btn-light">Back</a><button type="submit" name="submit" value="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>
</div>
@endsection
