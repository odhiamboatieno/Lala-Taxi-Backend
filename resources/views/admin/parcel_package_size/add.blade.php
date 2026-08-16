@extends('admin.template')

@section('title-sub', 'Add Package Size')
@section('pagetitle', 'Add Package Size')

@section('main')
<div id="layout-wrapper">
    <div class="card"><div class="card-header"><h4 class="mb-1">Add Package Size</h4></div><div class="card-body">
        <form method="POST" action="{{ url('admin/add_parcel_package_size') }}" class="row g-3">
            @csrf
            <div class="col-md-4"><label class="form-label">Key</label><input name="key" class="form-control" value="{{ old('key') }}"></div>
            <div class="col-md-4"><label class="form-label">Title</label><input name="title" class="form-control" value="{{ old('title') }}"></div>
            <div class="col-md-4"><label class="form-label">Subtitle</label><input name="subtitle" class="form-control" value="{{ old('subtitle') }}"></div>
            <div class="col-md-4"><label class="form-label">Price Multiplier</label><input name="price_multiplier" class="form-control" value="{{ old('price_multiplier', 1) }}"></div>
            <div class="col-md-4"><label class="form-label">Sort Order</label><input name="sort_order" class="form-control" value="{{ old('sort_order', 0) }}"></div>
            <div class="col-12"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="4">{{ old('description') }}</textarea></div>
            <div class="col-12"><div class="form-check"><input type="checkbox" name="is_active" class="form-check-input" id="size_active" {{ old('is_active', true) ? 'checked' : '' }}><label class="form-check-label" for="size_active">Active</label></div></div>
            <div class="col-12 d-flex justify-content-end gap-2"><a href="{{ url('admin/parcel_package_sizes') }}" class="btn btn-light">Back</a><button type="submit" name="submit" value="submit" class="btn btn-primary">Save</button></div>
        </form>
    </div></div>
</div>
@endsection
