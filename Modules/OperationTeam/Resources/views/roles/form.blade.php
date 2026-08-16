@extends('admin.template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>{{ $role->exists ? 'Edit Employee Role' : 'Add Employee Role' }}</h1>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-body">
                <form method="post" action="{{ $role->exists ? url('company/operation-team/roles/'.$role->id) : url('company/operation-team/roles') }}">
                    @csrf
                    @if($role->exists)
                        @method('PUT')
                    @endif
                    <div class="mb-3">
                        <label class="form-label">Role Name</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label d-block">Select Modules For Access</label>
                        <div class="row">
                            @foreach($permissionModules as $module => $permissions)
                                <div class="col-md-4 mb-3">
                                    <div class="border rounded p-3 h-100">
                                        <div class="fw-semibold mb-2">{{ ucwords(str_replace('_', ' ', $module)) }}</div>
                                        @foreach($permissions as $permission)
                                            <div class="form-check">
                                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission }}" id="{{ $module }}_{{ $loop->index }}"
                                                    {{ in_array($permission, old('permissions', $selectedPermissions), true) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="{{ $module }}_{{ $loop->index }}">{{ $permission }}</label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                    <div class="form-check form-switch mb-4">
                        <input type="checkbox" class="form-check-input" name="status" value="1" {{ old('status', $role->status ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                    <div class="text-end">
                        <a href="{{ url('company/operation-team/roles') }}" class="btn btn-secondary">Cancel</a>
                        <button class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
