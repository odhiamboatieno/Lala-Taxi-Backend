@extends('admin.template')

@section('title-sub', __('messages.admin.manage_riders_page.page_title'))
@section('pagetitle', __('messages.admin.manage_riders_page.page_title'))

@section('main')
@php
    $adminUser = Auth::guard('admin')->user();
    $companyUser = Auth::guard('company')->user();
    $baseSegment = LOGIN_USER_TYPE;
    $listUrl = url($baseSegment.'/rider');
    $addUrl = url($baseSegment.'/add_rider');
    $levelUrl = url($baseSegment.'/customer-levels');
    $companySaasEnabled = $companyUser && function_exists('company_saas_enabled') && company_saas_enabled();
    $canCreateRider = ($companyUser && (!current_operation_team_member() || operation_team_can('create_rider'))) || optional($adminUser)->can('create_rider');
    $canManageLevels = ($companySaasEnabled && (!current_operation_team_member() || operation_team_can('manage_rider_levels'))) || optional($adminUser)->can('manage_rider_levels') || optional($adminUser)->can('manage_admin');
    $canUpdateRider = ($companyUser && (!current_operation_team_member() || operation_team_can('update_rider'))) || optional($adminUser)->can('update_rider');
    $canDeleteRider = ($companyUser && (!current_operation_team_member() || operation_team_can('delete_rider'))) || optional($adminUser)->can('delete_rider');
@endphp
<div id="layout-wrapper">
    <div class="row g-4 mb-4">
        <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3 class="mb-1">{{ __('messages.admin.manage_riders_page.page_title') }}</h3>
                <p class="text-muted mb-0">{{ __('messages.admin.manage_riders_page.dashboard_description') }}</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if($canCreateRider)
                    <a class="btn btn-primary" href="{{ $addUrl }}">
                        <i class="ri-user-add-line me-1"></i>{{ __('messages.admin.manage_riders_page.add_customer') }}
                    </a>
                @endif
                @if($canManageLevels)
                    <a class="btn btn-light" href="{{ $levelUrl }}">
                        <i class="ri-award-line me-1"></i>{{ __('messages.admin.manage_riders_page.customer_levels') }}
                    </a>
                @endif
            </div>
        </div>

        @foreach([
            ['label' => __('messages.admin.manage_riders_page.total_customer'), 'value' => $summary['total'] ?? 0, 'icon' => 'ri-group-line', 'tone' => 'primary'],
            ['label' => __('messages.admin.manage_riders_page.inactive_customer'), 'value' => $summary['inactive'] ?? 0, 'icon' => 'ri-user-unfollow-line', 'tone' => 'danger'],
            ['label' => __('messages.admin.manage_riders_page.new_customer'), 'value' => $summary['new'] ?? 0, 'icon' => 'ri-user-add-line', 'tone' => 'info'],
            ['label' => __('messages.admin.manage_riders_page.active_customer'), 'value' => $summary['active'] ?? 0, 'icon' => 'ri-user-follow-line', 'tone' => 'success'],
        ] as $card)
            <div class="col-xl-3 col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="rounded-circle bg-{{ $card['tone'] }}-subtle text-{{ $card['tone'] }} d-inline-flex align-items-end justify-content-center mb-3" style="width:56px;height:56px;">
                            <i class="{{ $card['icon'] }} fs-3"></i>
                        </div>
                        <div class="display-6 fw-semibold mb-1">{{ $card['value'] }}</div>
                        <div class="text-muted">{{ $card['label'] }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-end flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_riders_page.customer_list') }}</h4>
                    <div class="text-muted">{{ __('messages.admin.manage_riders_page.customer_list_description') }}</div>
                </div>
                <div class="text-muted">{{ __('messages.admin.manage_riders_page.total_customer_label') }} <strong>{{ $summary['total'] ?? 0 }}</strong></div>
            </div>

            <form method="GET" action="{{ $listUrl }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">{{ __('messages.admin.manage_riders_page.search') }}</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('messages.admin.manage_riders_page.search_placeholder') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.admin.manage_riders_page.quick_status') }}</label>
                    <select name="status_scope" class="form-select">
                        <option value="">{{ __('messages.admin.manage_riders_page.all') }}</option>
                        <option value="active" {{ ($filters['status_scope'] ?? '') === 'active' ? 'selected' : '' }}>{{ __('messages.admin.manage_riders_page.active') }}</option>
                        <option value="inactive" {{ ($filters['status_scope'] ?? '') === 'inactive' ? 'selected' : '' }}>{{ __('messages.admin.manage_riders_page.inactive') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.admin.manage_riders_page.level') }}</label>
                    <select name="level_id" class="form-select">
                        <option value="">{{ __('messages.admin.manage_riders_page.all_levels') }}</option>
                        @foreach($level_options as $levelId => $levelName)
                            <option value="{{ $levelId }}" {{ (int) ($filters['level_id'] ?? 0) === (int) $levelId ? 'selected' : '' }}>{{ $levelName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">{{ __('messages.admin.manage_riders_page.search') }}</button>
                    <a href="{{ $listUrl }}" class="btn btn-light">{{ __('messages.admin.manage_riders_page.reset') }}</a>
                </div>
            </form>

            <div class="mb-4 d-flex flex-wrap gap-2">
                <a href="{{ $listUrl }}" class="btn btn-sm {{ empty($filters['level_id']) ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('messages.admin.manage_riders_page.all') }}</a>
                @foreach($level_cards as $card)
                    <a href="{{ url($baseSegment.'/rider?'.http_build_query(array_merge(request()->query(), ['level_id' => $card['id']]))) }}" class="btn btn-sm {{ (int) ($filters['level_id'] ?? 0) === (int) $card['id'] ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $card['name'] }}
                    </a>
                @endforeach
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('messages.admin.manage_riders_page.sl') }}</th>
                            <th>{{ __('messages.admin.manage_riders_page.customer_name') }}</th>
                            <th>{{ __('messages.admin.manage_riders_page.profile_status') }}</th>
                            <th>{{ __('messages.admin.manage_riders_page.contact_info') }}</th>
                            <th>{{ __('messages.admin.manage_riders_page.level') }}</th>
                            <th>{{ __('messages.admin.manage_riders_page.total_trip') }}</th>
                            <th>{{ __('messages.admin.manage_riders_page.status') }}</th>
                            <th class="text-end">{{ __('messages.admin.manage_riders_page.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($riders as $index => $rider)
                            <tr>
                                <td>{{ $riders->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-end gap-3">
                                        <img src="{{ optional($rider->profile_picture)->src ?: url('images/user.jpeg') }}" alt="{{ $rider->first_name }}" class="rounded-circle border" style="width:42px;height:42px;object-fit:cover;">
                                        <div>
                                            <div class="fw-semibold">{{ $rider->first_name }} {{ $rider->last_name }}</div>
                                            <div class="text-muted small">{{ optional($rider->rider_location)->home ?: __('messages.admin.manage_riders_page.no_home_location') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-warning-subtle text-warning">{{ $rider->profile_completion ?? 0 }}%</span>
                                </td>
                                <td>
                                    <div>+{{ $rider->country_code }}{{ $rider->mobile_number }}</div>
                                    <div class="text-muted small">{{ protectedString($rider->email) }}</div>
                                </td>
                                <td>{{ $rider->level_badge }}</td>
                                <td>{{ $rider->completed_trips_count ?? 0 }}</td>
                                <td>
                                    <span class="badge {{ $rider->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                        {{ $rider->status }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url($baseSegment.'/edit_rider/'.$rider->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if($canUpdateRider)
                                            <a href="{{ url($baseSegment.'/edit_rider/'.$rider->id) }}" class="btn btn-sm btn-outline-success">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                        @endif
                                        @if($canDeleteRider)
                                            <a href="{{ url($baseSegment.'/delete_rider/'.$rider->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.admin.manage_riders_page.delete_customer_confirm') }}')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="text-muted mb-2">{{ __('messages.admin.manage_riders_page.no_customers_found') }}</div>
                                    @if($canCreateRider)
                                        <a class="btn btn-primary btn-sm" href="{{ $addUrl }}">{{ __('messages.admin.manage_riders_page.create_customer') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($riders->hasPages())
                <div class="d-flex justify-content-end mt-3">
                    {{ $riders->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
