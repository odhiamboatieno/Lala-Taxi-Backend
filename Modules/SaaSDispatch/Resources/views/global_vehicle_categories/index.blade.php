@extends('admin.template')

@section('title-sub', 'Global Vehicle Categories')
@section('pagetitle', 'Global Vehicle Categories')

@section('main')
<div id="layout-wrapper">
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Add Category</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('saasdispatch.global-vehicle-categories.store') }}" class="row g-3">
                        @csrf
                        <div class="col-12"><input class="form-control" name="name" placeholder="Name" required></div>
                        <div class="col-12"><input class="form-control" name="slug" placeholder="Slug" required></div>
                        <div class="col-12"><input class="form-control" name="icon" placeholder="Icon path or name"></div>
                        <div class="col-12"><textarea class="form-control" name="description" rows="3" placeholder="Description"></textarea></div>
                        <div class="col-6"><input class="form-control" type="number" name="sort_order" value="0" min="0"></div>
                        <div class="col-6">
                            <select class="form-select" name="status">
                                <option>Active</option>
                                <option>Inactive</option>
                            </select>
                        </div>
                        <div class="col-12 form-check">
                            <input class="form-check-input" type="checkbox" name="is_visible_to_rider" value="1" id="is_visible_to_rider" checked>
                            <label class="form-check-label" for="is_visible_to_rider">Visible to riders</label>
                        </div>
                        <div class="col-12"><button class="btn btn-primary">Create</button></div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Categories</h5></div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped align-middle mb-0">
                            <thead><tr><th>Name</th><th>Slug</th><th>Visible</th><th>Status</th><th>Sort</th><th></th></tr></thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td>{{ $category->name }}</td>
                                        <td>{{ $category->slug }}</td>
                                        <td>{{ $category->is_visible_to_rider ? 'Yes' : 'No' }}</td>
                                        <td>{{ $category->status }}</td>
                                        <td>{{ $category->sort_order }}</td>
                                        <td class="text-end">
                                            <form method="POST" action="{{ route('saasdispatch.global-vehicle-categories.destroy', $category) }}" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this category?')">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td colspan="6">
                                            <form method="POST" action="{{ route('saasdispatch.global-vehicle-categories.update', $category) }}" class="row g-2">
                                                @csrf
                                                @method('PUT')
                                                <div class="col-md-3"><input class="form-control form-control-sm" name="name" value="{{ $category->name }}" required></div>
                                                <div class="col-md-2"><input class="form-control form-control-sm" name="slug" value="{{ $category->slug }}" required></div>
                                                <div class="col-md-2"><input class="form-control form-control-sm" name="icon" value="{{ $category->icon }}"></div>
                                                <div class="col-md-2"><input class="form-control form-control-sm" type="number" name="sort_order" value="{{ $category->sort_order }}"></div>
                                                <div class="col-md-2">
                                                    <select class="form-select form-select-sm" name="status">
                                                        <option {{ $category->status === 'Active' ? 'selected' : '' }}>Active</option>
                                                        <option {{ $category->status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-1"><button class="btn btn-sm btn-primary w-100">Save</button></div>
                                                <div class="col-12"><textarea class="form-control form-control-sm" name="description" rows="2">{{ $category->description }}</textarea></div>
                                                <div class="col-12 form-check ms-2">
                                                    <input class="form-check-input" type="checkbox" name="is_visible_to_rider" value="1" id="visible_{{ $category->id }}" {{ $category->is_visible_to_rider ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="visible_{{ $category->id }}">Visible to riders</label>
                                                </div>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center text-muted py-4">No categories found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
