@extends('admin.template')

@section('title-sub', 'Edit Package Size')
@section('pagetitle', 'Edit Package Size')

@section('main')
<div id="layout-wrapper">
    <div class="card"><div class="card-header"><h4 class="mb-1">Edit Package Size</h4></div><div class="card-body">
        <form method="POST" action="{{ url('admin/edit_parcel_package_size/'.$packageSize->id) }}" class="row g-3">
            @csrf
            @method('PUT')
            <div class="col-md-4"><label class="form-label">Key</label><input name="key" class="form-control" value="{{ old('key', $packageSize->key) }}"></div>
            <div class="col-md-4"><label class="form-label">Title</label><input name="title" class="form-control" value="{{ old('title', $packageSize->title) }}"></div>
            <div class="col-md-4"><label class="form-label">Subtitle</label><input name="subtitle" class="form-control" value="{{ old('subtitle', $packageSize->subtitle) }}"></div>
            <div class="col-md-4"><label class="form-label">Price Multiplier</label><input name="price_multiplier" class="form-control" value="{{ old('price_multiplier', $packageSize->price_multiplier) }}"></div>
            <div class="col-md-4"><label class="form-label">Sort Order</label><input name="sort_order" class="form-control" value="{{ old('sort_order', $packageSize->sort_order) }}"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4">{{ old('description', $packageSize->description) }}</textarea></div>
            <div class="col-12"><div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" id="size_active" {{ old('is_active', $packageSize->is_active) ? 'checked' : '' }}><label class="form-check-label" for="size_active">Active</label></div></div>
            <div class="col-12 d-flex justify-content-end gap-2"><a href="{{ url('admin/parcel_package_sizes') }}" class="btn btn-light">Back</a><button type="submit" name="submit" value="submit" class="btn btn-primary">Update</button></div>
        </form>
    </div></div>
</div>
@endsection
