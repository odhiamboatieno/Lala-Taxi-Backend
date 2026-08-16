@extends('admin.template')

@section('title-sub', 'SaaS Feature Modules')
@section('pagetitle', 'SaaS Feature Modules')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h4 class="mb-1">SaaS Feature Modules</h4>
                <p class="text-muted mb-0">Install, enable, or disable the optional SaaS company toolsets.</p>
            </div>
            <a href="{{ route('saasdispatch.dashboard') }}" class="btn btn-light">Back</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Module</th>
                            <th>Description</th>
                            <th>Installed</th>
                            <th>Enabled</th>
                            <th>Status</th>
                            <th class="text-end">Save</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $module)
                            <tr>
                                <td class="fw-semibold">{{ $module->name }}</td>
                                <td class="text-muted">{{ $module->description }}</td>
                                <td colspan="4">
                                    <form method="POST" action="{{ route('saasdispatch.modules.update', $module) }}" class="d-flex flex-wrap gap-4 align-items-center justify-content-between">
                                        @csrf
                                        @method('PUT')
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="is_installed" value="0">
                                            <input class="form-check-input" type="checkbox" name="is_installed" value="1" {{ $module->is_installed ? 'checked' : '' }}>
                                        </div>
                                        <div class="form-check form-switch">
                                            <input type="hidden" name="is_enabled" value="0">
                                            <input class="form-check-input" type="checkbox" name="is_enabled" value="1" {{ $module->is_enabled ? 'checked' : '' }}>
                                        </div>
                                        <div>
                                            <span class="badge {{ $module->is_enabled ? 'bg-success' : 'bg-secondary' }}">{{ $module->status }}</span>
                                        </div>
                                        <div class="ms-auto">
                                            <button class="btn btn-primary btn-sm">Update</button>
                                        </div>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-5">No feature modules found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
