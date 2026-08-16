@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h2 class="fw-semibold mb-0">{{ __('messages.admin.manage_vehicle_page.vehicle_list') }}</h2>
        </div>

        @forelse($vehicleTypeStats as $stat)
            <div class="col-xl-6">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body d-flex align-items-start justify-content-between">
                        <div>
                            <div class="fs-4 fw-semibold text-primary mb-3">{{ $stat['name'] }}</div>
                            <div class="display-6 fw-bold mb-0">{{ $stat['total'] }}</div>
                        </div>
                        <div class="text-end">
                            @if($stat['image'])
                                <img src="{{ $stat['image'] }}" alt="{{ $stat['name'] }}" style="width:56px;height:56px;object-fit:contain;">
                            @else
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center" style="width:56px;height:56px;">
                                    <i class="bi bi-truck-front fs-3 text-primary"></i>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-muted">{{ __('messages.admin.manage_vehicle_page.no_vehicle_type_stats') }}</div>
                </div>
            </div>
        @endforelse
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
                <div>
                    <h3 class="fw-semibold mb-2">{{ __('messages.admin.manage_vehicle_page.all_vehicles') }}</h3>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ url(LOGIN_USER_TYPE.'/vehicle?status_scope=all&search='.urlencode($search)) }}" class="btn {{ $statusScope === 'all' ? 'btn-primary' : 'btn-light' }}">{{ __('messages.admin.manage_vehicle_page.all') }}</a>
                        <a href="{{ url(LOGIN_USER_TYPE.'/vehicle?status_scope=active&search='.urlencode($search)) }}" class="btn {{ $statusScope === 'active' ? 'btn-primary' : 'btn-light' }}">{{ __('messages.admin.manage_vehicle_page.active') }}</a>
                        <a href="{{ url(LOGIN_USER_TYPE.'/vehicle?status_scope=inactive&search='.urlencode($search)) }}" class="btn {{ $statusScope === 'inactive' ? 'btn-primary' : 'btn-light' }}">{{ __('messages.admin.manage_vehicle_page.inactive') }}</a>
                    </div>
                </div>
                <div class="text-muted fs-5">{{ __('messages.admin.manage_vehicle_page.total_vehicle') }} <span class="fw-semibold text-dark">{{ $totalVehicles }}</span></div>
            </div>

            <div class="card bg-light-subtle border-0 mb-4">
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                        <form method="GET" action="{{ url(LOGIN_USER_TYPE.'/vehicle') }}" class="d-flex flex-wrap gap-2 align-items-center">
                            <input type="hidden" name="status_scope" value="{{ $statusScope }}">
                            <div class="input-group" style="min-width: 300px;">
                                <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                                <input type="text" class="form-control border-start-0" name="search" value="{{ $search }}" placeholder="{{ __('messages.admin.manage_vehicle_page.search_placeholder') }}">
                            </div>
                            <button class="btn btn-primary px-4" type="submit">{{ __('messages.admin.manage_vehicle_page.search') }}</button>
                        </form>

                        <div class="d-flex flex-wrap gap-2 align-items-center">
                            <a href="{{ url(LOGIN_USER_TYPE.'/vehicle') }}" class="btn btn-outline-primary"><i class="bi bi-arrow-clockwise"></i></a>
                            <a href="{{ url(LOGIN_USER_TYPE.'/add_vehicle') }}" class="btn btn-primary px-4">
                                <i class="bi bi-plus-circle me-1"></i> {{ __('messages.admin.manage_vehicle_page.add_new_vehicle') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('messages.admin.manage_vehicle_page.sl') }}</th>
                            <th>{{ __('messages.admin.manage_vehicle_page.vehicle_id') }}</th>
                            <th>{{ __('messages.admin.manage_vehicle_page.driver_name') }}</th>
                            <th>{{ __('messages.admin.manage_vehicle_page.vehicle_type') }}</th>
                            <th>{{ __('messages.admin.manage_vehicle_page.brand_model') }}</th>
                            <th>{{ __('messages.admin.manage_vehicle_page.license') }}</th>
                            <th>{{ __('messages.admin.manage_vehicle_page.owner') }}</th>
                            <th>{{ __('messages.admin.manage_vehicle_page.vehicle_features') }}</th>
                            <th>{{ __('messages.admin.manage_vehicle_page.status') }}</th>
                            <th>{{ __('messages.admin.manage_vehicle_page.action') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($vehicles as $index => $vehicle)
                            <tr>
                                <td>{{ $vehicles->firstItem() + $index }}</td>
                                <td>#{{ str_pad($vehicle->id, 5, '0', STR_PAD_LEFT) }}</td>
                                <td>
                                    <div class="fw-semibold">{{ $vehicle->driver_display_name }}</div>
                                    <small class="text-muted">{{ optional($vehicle->user)->phone_number ?: optional($vehicle->user)->mobile_number ?: __('messages.admin.manage_vehicle_page.no_phone') }}</small>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        @forelse($vehicle->vehicle_type_names as $name)
                                            <span class="badge bg-light text-dark border">{{ $name }}</span>
                                        @empty
                                            <span class="text-muted">{{ __('messages.admin.not_provided') }}</span>
                                        @endforelse
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold">{{ $vehicle->vehicle_name ?: __('messages.admin.not_provided') }}</div>
                                    <small class="text-muted">
                                        {{ optional($vehicle->make)->make_vehicle_name ?: __('messages.admin.manage_vehicle_page.unknown_make') }}
                                        -
                                        {{ optional($vehicle->model)->model_name ?: __('messages.admin.manage_vehicle_page.unknown_model') }}
                                    </small>
                                </td>
                                <td>{{ $vehicle->vehicle_number ?: '-' }}</td>
                                <td>{{ $vehicle->owner_label }}</td>
                                <td>
                                    @if($vehicle->feature_lines->isNotEmpty())
                                        @foreach($vehicle->feature_lines as $feature)
                                            <div>{{ $feature }}</div>
                                        @endforeach
                                    @else
                                        <span class="text-muted">{{ __('messages.admin.manage_vehicle_page.no_extra_features') }}</span>
                                    @endif
                                </td>
                                <td>
                                    <form method="POST" action="{{ url(LOGIN_USER_TYPE.'/vehicle/toggle-status/'.$vehicle->id) }}">
                                        {{ csrf_field() }}
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" onchange="this.form.submit()" {{ $vehicle->status === 'Active' ? 'checked' : '' }}>
                                        </div>
                                    </form>
                                </td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ url(LOGIN_USER_TYPE.'/edit_vehicle/'.$vehicle->id) }}" class="btn btn-sm btn-outline-success" title="{{ __('messages.admin.manage_vehicle_page.view_vehicle') }}">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="{{ url(LOGIN_USER_TYPE.'/edit_vehicle/'.$vehicle->id) }}" class="btn btn-sm btn-outline-primary" title="{{ __('messages.admin.manage_vehicle_page.edit_vehicle') }}">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <a href="{{ url(LOGIN_USER_TYPE.'/delete_vehicle/'.$vehicle->id) }}" class="btn btn-sm btn-outline-danger" onclick="return confirm('{{ __('messages.admin.manage_vehicle_page.delete_vehicle_confirm') }}')" title="{{ __('messages.admin.manage_vehicle_page.delete_vehicle') }}">
                                            <i class="bi bi-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    {{ __('messages.admin.manage_vehicle_page.no_vehicles_found') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($vehicles->hasPages())
                <div class="d-flex justify-content-end mt-4">
                    {{ $vehicles->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
