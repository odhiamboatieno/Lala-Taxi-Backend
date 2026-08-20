@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row g-4">
        <div class="col-12">
            <h2 class="fw-semibold mb-0">{{ __('messages.admin.manage_roles_page.employee_attributes') }}</h2>
        </div>

        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                        <div>
                            <h4 class="fw-semibold mb-1">{{ __('messages.admin.manage_roles_page.employee_role_access_permissions') }}</h4>
                            <div class="text-muted">{{ __('messages.admin.manage_roles_page.page_description') }}</div>
                        </div>
                        <a href="{{ url('admin/add_role') }}" class="btn btn-primary">
                            <i class="bi bi-plus-circle me-1"></i> {{ __('messages.admin.manage_roles_page.add_role') }}
                        </a>
                    </div>

                    <form method="GET" action="{{ url('admin/roles') }}" class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <div class="input-group" style="min-width: 300px;">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control border-start-0" name="search" value="{{ $search }}" placeholder="{{ __('messages.admin.manage_roles_page.search_by_role_name') }}">
                            </div>
                            <button class="btn btn-primary px-4" type="submit">{{ __('messages.admin.manage_roles_page.search') }}</button>
                        </div>
                        <div class="text-muted fs-5">{{ __('messages.admin.manage_roles_page.total_designation') }} : <span class="fw-semibold text-dark">{{ $totalRoles }}</span></div>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>{{ __('messages.admin.manage_roles_page.sl') }}</th>
                                    <th>{{ __('messages.admin.manage_roles_page.id') }}</th>
                                    <th>{{ __('messages.admin.manage_roles_page.role_name') }}</th>
                                    <th>{{ __('messages.admin.manage_roles_page.module_access') }}</th>
                                    <th>{{ __('messages.admin.manage_roles_page.users') }}</th>
                                    <th>{{ __('messages.admin.manage_roles_page.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($roles as $index => $role)
                                    <tr>
                                        <td>{{ $roles->firstItem() + $index }}</td>
                                        <td>#{{ 1000 + $role->id }}</td>
                                        <td>
                                            <div class="fw-semibold">{{ $role->display_name ?: $role->name }}</div>
                                            <small class="text-muted">{{ $role->description }}</small>
                                        </td>
                                        <td style="min-width: 360px;">
                                            @if($role->permission_labels->isNotEmpty())
                                                {{ $role->modules_summary }}
                                                @if($role->permission_labels->count() > 8)
                                                    <span class="text-muted"> +{{ $role->permission_labels->count() - 8 }} {{ __('messages.admin.manage_roles_page.more') }}</span>
                                                @endif
                                            @else
                                                <span class="text-muted">{{ __('messages.admin.manage_roles_page.no_module_access_assigned') }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $role->users_count }}</td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ url('admin/edit_role/'.$role->id) }}" class="btn btn-sm btn-outline-success" title="{{ __('messages.admin.manage_roles_page.view_role') }}">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ url('admin/edit_role/'.$role->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.admin.manage_roles_page.edit_role') }}">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                                <a href="{{ url('admin/delete_role/'.$role->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.admin.manage_roles_page.confirm_delete_role') }}')" title="{{ __('messages.admin.manage_roles_page.delete_role') }}">
                                                    <i class="bi bi-trash"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">{{ __('messages.admin.manage_roles_page.no_roles_found') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($roles->hasPages())
                        <div class="d-flex justify-content-end mt-4">
                            {{ $roles->links('pagination::bootstrap-5') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
