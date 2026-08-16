@extends('admin.template')

@section('title-sub', __('messages.admin.manage_drivers_page.driver_operations'))
@section('pagetitle', __('messages.admin.manage_drivers_page.driver_operations'))

@section('main')
@php
    $companySaasEnabled = LOGIN_USER_TYPE == 'company' && function_exists('company_saas_enabled') && company_saas_enabled();
@endphp
<div id="layout-wrapper">
    <div class="row g-4 mb-4">
        <div class="col-12 d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h3 class="mb-1">{{ __('messages.admin.manage_drivers_page.driver_analytical_data') }}</h3>
                <p class="text-muted mb-0">{{ __('messages.admin.manage_drivers_page.analytical_data_desc') }}</p>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                @if((LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->status == 'Active') || (LOGIN_USER_TYPE=='admin' && Auth::guard('admin')->user()->can('create_driver')))
                    <a class="btn btn-primary" href="{{ url(LOGIN_USER_TYPE.'/add_driver') }}">
                        <i class="ri-user-add-line me-1"></i>{{ __('messages.admin.manage_drivers_page.add_driver') }}
                    </a>
                @endif
                @if(($companySaasEnabled && (!current_operation_team_member() || operation_team_can('manage_driver_levels'))) || (LOGIN_USER_TYPE == 'admin' && (optional(Auth::guard('admin')->user())->can('manage_driver_levels') || optional(Auth::guard('admin')->user())->can('manage_admin'))))
                    <a class="btn btn-light" href="{{ url(LOGIN_USER_TYPE.'/driver-levels') }}">
                        <i class="ri-award-line me-1"></i>{{ __('messages.admin.manage_drivers_page.driver_levels') }}
                    </a>
                @endif
            </div>
        </div>

        @foreach([
            ['label' => __('messages.admin.manage_drivers_page.total_driver_card'), 'value' => $summary['total'] ?? 0, 'icon' => 'ri-team-line', 'tone' => 'primary'],
            ['label' => __('messages.admin.manage_drivers_page.active_driver_card'), 'value' => $summary['active'] ?? 0, 'icon' => 'ri-user-follow-line', 'tone' => 'success'],
            ['label' => __('messages.admin.manage_drivers_page.inactive_driver_card'), 'value' => $summary['inactive'] ?? 0, 'icon' => 'ri-user-unfollow-line', 'tone' => 'danger'],
            ['label' => __('messages.admin.manage_drivers_page.car_driver_card'), 'value' => $summary['car_drivers'] ?? 0, 'icon' => 'ri-taxi-line', 'tone' => 'info'],
            ['label' => __('messages.admin.manage_drivers_page.motorbike_driver_card'), 'value' => $summary['bike_drivers'] ?? 0, 'icon' => 'ri-motorbike-line', 'tone' => 'warning'],
        ] as $card)
            <div class="col-xl col-md-6">
                <div class="card h-100">
                    <div class="card-body">
                        <div class="rounded-circle bg-{{ $card['tone'] }}-subtle text-{{ $card['tone'] }} d-inline-flex align-items-center justify-content-center mb-3" style="width:56px;height:56px;">
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
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <div>
                    <h4 class="mb-1">{{ __('messages.admin.manage_drivers_page.driver_list') }}</h4>
                    <div class="text-muted">{{ __('messages.admin.manage_drivers_page.driver_list_desc') }}</div>
                </div>
                <div class="text-muted">{{ __('messages.admin.manage_drivers_page.total_driver_label') }} <strong>{{ $summary['total'] ?? 0 }}</strong></div>
            </div>

            <form method="GET" action="{{ url(LOGIN_USER_TYPE.'/driver') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.search_label') }}</label>
                    <input type="text" name="search" class="form-control" value="{{ $filters['search'] ?? '' }}" placeholder="{{ __('messages.admin.manage_drivers_page.search_placeholder') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.quick_status') }}</label>
                    <select name="status_scope" class="form-select">
                        <option value="">{{ __('messages.admin.manage_drivers_page.all_option') }}</option>
                        <option value="active" {{ ($filters['status_scope'] ?? '') === 'active' ? 'selected' : '' }}>{{ __('messages.admin.manage_drivers_page.active_option') }}</option>
                        <option value="inactive" {{ ($filters['status_scope'] ?? '') === 'inactive' ? 'selected' : '' }}>{{ __('messages.admin.manage_drivers_page.inactive_option') }}</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">{{ __('messages.admin.manage_drivers_page.level_label') }}</label>
                    <select name="level_id" class="form-select">
                        <option value="">{{ __('messages.admin.manage_drivers_page.all_levels') }}</option>
                        @foreach($level_options as $levelId => $levelName)
                            <option value="{{ $levelId }}" {{ (int) ($filters['level_id'] ?? 0) === (int) $levelId ? 'selected' : '' }}>{{ $levelName }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary flex-grow-1">{{ __('messages.admin.manage_drivers_page.search_button') }}</button>
                    <a href="{{ url(LOGIN_USER_TYPE.'/driver') }}" class="btn btn-light">{{ __('messages.admin.manage_drivers_page.reset_button') }}</a>
                </div>
            </form>

            <div class="mb-4 d-flex flex-wrap gap-2">
                <a href="{{ url(LOGIN_USER_TYPE.'/driver') }}" class="btn btn-sm {{ empty($filters['level_id']) ? 'btn-primary' : 'btn-outline-primary' }}">{{ __('messages.admin.manage_drivers_page.all_option') }}</a>
                @foreach($level_cards as $card)
                    <a href="{{ url(LOGIN_USER_TYPE.'/driver?'.http_build_query(array_merge(request()->query(), ['level_id' => $card['id']]))) }}" class="btn btn-sm {{ (int) ($filters['level_id'] ?? 0) === (int) $card['id'] ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $card['name'] }}
                    </a>
                @endforeach
            </div>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('messages.admin.manage_drivers_page.sl') }}</th>
                            <th>{{ __('messages.admin.manage_drivers_page.name_header') }}</th>
                            <th>{{ __('messages.admin.manage_drivers_page.contact_info') }}</th>
                            <th>{{ __('messages.admin.manage_drivers_page.profile_status') }}</th>
                            <th>{{ __('messages.admin.manage_drivers_page.level_header') }}</th>
                            <th>{{ __('messages.admin.manage_drivers_page.total_trip') }}</th>
                            <th>{{ __('messages.admin.manage_drivers_page.earning_header') }}</th>
                            <th>{{ __('messages.admin.manage_drivers_page.status_header') }}</th>
                            <th class="text-end">{{ __('messages.admin.manage_drivers_page.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($drivers as $index => $driver)
                            <tr>
                                <td>{{ $drivers->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ optional($driver->profile_picture)->src ?: url('images/user.jpeg') }}" alt="{{ $driver->first_name }}" class="rounded-circle border" style="width:42px;height:42px;object-fit:cover;">
                                        <div>
                                            <div class="fw-semibold">{{ $driver->first_name }} {{ $driver->last_name }}</div>
                                            <div class="text-muted small">{{ optional($driver->company)->name ?: __('messages.admin.manage_drivers_page.admin_global') }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>+{{ $driver->country_code }}{{ $driver->mobile_number }}</div>
                                    <div class="text-muted small">{{ protectedString($driver->email) }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-warning-subtle text-warning">{{ $driver->profile_completion ?? 0 }}%</span>
                                </td>
                                <td>{{ $driver->level_badge }}</td>
                                <td>{{ $driver->completed_trips_count ?? 0 }}</td>
                                <td>{{ html_entity_decode(currency_symbol()) }}{{ number_format((float) ($driver->total_earnings_amount ?? 0), 2) }}</td>
                                <td>
                                    @if(method_exists($driver, 'trashed') && $driver->trashed())
                                        <span class="badge bg-dark-subtle text-dark">{{ __('messages.admin.manage_drivers_page.deleted') }}</span>
                                    @else
                                        <span class="badge {{ $driver->status === 'Active' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            {{ $driver->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ url(LOGIN_USER_TYPE.'/edit_driver/'.$driver->id) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="ri-eye-line"></i>
                                        </a>
                                        @if((LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->status == 'Active') || (LOGIN_USER_TYPE=='admin' && Auth::guard('admin')->user()->can('update_driver')))
                                            <a href="{{ url(LOGIN_USER_TYPE.'/edit_driver/'.$driver->id) }}" class="btn btn-sm btn-outline-success">
                                                <i class="ri-pencil-line"></i>
                                            </a>
                                        @endif
                                        @if((LOGIN_USER_TYPE=='company') || (LOGIN_USER_TYPE=='admin' && Auth::guard('admin')->user()->can('delete_driver')))
                                            <a href="{{ url(LOGIN_USER_TYPE.'/delete_driver/'.$driver->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.admin.manage_drivers_page.delete_driver_confirm') }}')">
                                                <i class="ri-delete-bin-line"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5">
                                    <div class="text-muted mb-2">{{ __('messages.admin.manage_drivers_page.no_drivers_found') }}</div>
                                    @if((LOGIN_USER_TYPE=='company' && Auth::guard('company')->user()->status == 'Active') || (LOGIN_USER_TYPE=='admin' && Auth::guard('admin')->user()->can('create_driver')))
                                        <a class="btn btn-primary btn-sm" href="{{ url(LOGIN_USER_TYPE.'/add_driver') }}">{{ __('messages.admin.manage_drivers_page.create_driver') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($drivers->hasPages())
                <div class="d-flex justify-content-end mt-3">
                    {{ $drivers->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
