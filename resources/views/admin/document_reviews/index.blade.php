@extends('admin.template')

@section('main')
@php
    $basePrefix = LOGIN_USER_TYPE;
    $navItems = [
        ['key' => 'driver', 'label' => __('messages.admin.document_reviews_page.nav_driver_documents'), 'url' => url($basePrefix.'/document-reviews/driver-documents')],
        ['key' => 'vehicle', 'label' => __('messages.admin.document_reviews_page.nav_vehicle_documents'), 'url' => url($basePrefix.'/document-reviews/vehicle-documents')],
        ['key' => 'company', 'label' => __('messages.admin.document_reviews_page.nav_company_documents'), 'url' => url($basePrefix.'/document-reviews/company-documents')],
        ['key' => 'profile_image', 'label' => __('messages.admin.document_reviews_page.nav_profile_images'), 'url' => url($basePrefix.'/document-reviews/profile-images')],
    ];
    $statusOptions = $scope === 'profile_image'
        ? ['pending' => __('messages.admin.document_reviews_page.status_pending'), 'approved' => __('messages.admin.document_reviews_page.status_approved'), 'rejected' => __('messages.admin.document_reviews_page.status_rejected')]
        : ['0' => __('messages.admin.document_reviews_page.status_pending'), '1' => __('messages.admin.document_reviews_page.status_approved'), '2' => __('messages.admin.document_reviews_page.status_rejected')];
@endphp

<main id="js-page-content" role="main" class="page-content">
    <div class="container-fluid py-4">
        <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-4">
            <div>
                <h1 class="h3 mb-1">{{ $title }}</h1>
                <p class="text-muted mb-0">{{ $subtitle }}</p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @foreach($navItems as $item)
                    <a href="{{ $item['url'] }}" class="btn {{ $scope === $item['key'] ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3 align-items-end">
                    <div class="col-lg-5">
                        <label class="form-label">{{ __('messages.admin.document_reviews_page.search_label') }}</label>
                        <input type="text" class="form-control" name="q" value="{{ $filters['q'] ?? '' }}" placeholder="{{ __('messages.admin.document_reviews_page.search_placeholder') }}">
                    </div>
                    <div class="col-lg-3">
                        <label class="form-label">{{ __('messages.admin.document_reviews_page.status_label') }}</label>
                        <select class="form-select" name="status">
                            <option value="">{{ __('messages.admin.document_reviews_page.status_all') }}</option>
                            @foreach($statusOptions as $value => $label)
                                <option value="{{ $value }}" {{ ($filters['status'] ?? '') === (string) $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-lg-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary">{{ __('messages.admin.document_reviews_page.filter_button') }}</button>
                        <a href="{{ url()->current() }}" class="btn btn-light">{{ __('messages.admin.document_reviews_page.reset_button') }}</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('messages.admin.document_reviews_page.th_owner') }}</th>
                                <th>{{ __('messages.admin.document_reviews_page.th_document') }}</th>
                                @if($scope === 'vehicle')
                                    <th>{{ __('messages.admin.document_reviews_page.th_vehicle') }}</th>
                                @endif
                                <th>{{ __('messages.admin.document_reviews_page.th_status') }}</th>
                                <th>{{ __('messages.admin.document_reviews_page.th_expiry') }}</th>
                                <th>{{ __('messages.admin.document_reviews_page.th_file') }}</th>
                                <th>{{ __('messages.admin.document_reviews_page.th_review') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($records as $record)
                                @php
                                    $user = $users[$record->user_id] ?? null;
                                    $vehicle = $vehicles[$record->vehicle_id] ?? null;
                                    $company = $companies[$record->company_id] ?? ($user ? $user->company : null);
                                    $statusValue = (string) ($scope === 'profile_image' ? ($record->approval_status ?? 'pending') : ($record->status ?? '0'));
                                    $statusLabel = $scope === 'profile_image'
                                        ? __('messages.admin.document_reviews_page.status_' . $statusValue)
                                        : ($statusValue === '1' ? __('messages.admin.document_reviews_page.status_approved') : ($statusValue === '2' ? __('messages.admin.document_reviews_page.status_rejected') : __('messages.admin.document_reviews_page.status_pending')));
                                    $statusClass = $statusValue === '1' || $statusValue === 'approved'
                                        ? 'bg-success-subtle text-success'
                                        : (($statusValue === '2' || $statusValue === 'rejected') ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning');
                                    $fileUrl = $scope === 'profile_image' ? ($record->pending_src_url ?: $record->src) : $record->document;
                                    $vehicleLabel = trim((string) ($vehicle->vehicle_name ?? ''));
                                    if ($vehicleLabel === '') {
                                        $vehicleLabel = trim((string) ($vehicle->vehicle_type ?? ''));
                                    }
                                    if ($vehicleLabel === '') {
                                        $vehicleLabel = trim((string) ($vehicle->vehicle_number ?? ''));
                                    }
                                    if ($vehicleLabel === '') {
                                        $vehicleLabel = __('messages.admin.document_reviews_page.vehicle_prefix') . $record->vehicle_id;
                                    }
                                @endphp
                                <tr>
                                    <td>
                                        @if($scope === 'company')
                                            <div class="fw-semibold">{{ $company->name ?? __('messages.admin.document_reviews_page.company_prefix') . $record->company_id }}</div>
                                            <div class="small text-muted">{{ $company->email ?? '' }}</div>
                                        @else
                                            <div class="fw-semibold">{{ trim(($user->first_name ?? '').' '.($user->last_name ?? '')) ?: __('messages.admin.document_reviews_page.driver_prefix') . $record->user_id }}</div>
                                            <div class="small text-muted">{{ $company->name ?? __('messages.admin.document_reviews_page.no_company') }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $scope === 'profile_image' ? __('messages.admin.document_reviews_page.profile_image_change') : ($record->document_name ?? __('messages.admin.document_reviews_page.document_name_fallback')) }}</div>
                                        @if(!empty($record->rejection_reason))
                                            <div class="small text-danger">{{ $record->rejection_reason }}</div>
                                        @endif
                                    </td>
                                    @if($scope === 'vehicle')
                                        <td>
                                            <div class="fw-semibold">{{ $vehicleLabel }}</div>
                                            <div class="small text-muted">{{ $vehicle->vehicle_number ?? '' }}</div>
                                        </td>
                                    @endif
                                    <td><span class="badge rounded-pill {{ $statusClass }}">{{ $statusLabel }}</span></td>
                                    <td>{{ !empty($record->expired_date) ? date('M d, Y', strtotime($record->expired_date)) : __('messages.admin.not_provided') }}</td>
                                    <td>
                                        @if($fileUrl)
                                            <a href="{{ $fileUrl }}" target="_blank" class="btn btn-sm btn-outline-secondary">{{ __('messages.admin.document_reviews_page.view_file') }}</a>
                                        @else
                                            <span class="text-muted">{{ __('messages.admin.document_reviews_page.no_file') }}</span>
                                        @endif
                                    </td>
                                    <td style="min-width: 280px;">
                                        <form method="POST" action="{{ $scope === 'company'
                                            ? url($basePrefix.'/document-reviews/company-documents/'.$record->id)
                                            : ($scope === 'profile_image'
                                                ? url($basePrefix.'/document-reviews/profile-images/'.$record->user_id)
                                                : ($scope === 'vehicle'
                                                    ? url($basePrefix.'/document-reviews/vehicle-documents/'.$record->id)
                                                    : url($basePrefix.'/document-reviews/driver-documents/'.$record->id))) }}">
                                            @csrf
                                            <div class="d-flex flex-column gap-2">
                                                <select class="form-select form-select-sm" name="status">
                                                    @if($scope === 'profile_image')
                                                        <option value="approved">{{ __('messages.admin.document_reviews_page.approve') }}</option>
                                                        <option value="rejected">{{ __('messages.admin.document_reviews_page.reject') }}</option>
                                                    @else
                                                        <option value="1">{{ __('messages.admin.document_reviews_page.approve') }}</option>
                                                        <option value="2">{{ __('messages.admin.document_reviews_page.reject') }}</option>
                                                    @endif
                                                </select>
                                                <textarea class="form-control form-control-sm" rows="2" name="rejection_reason" placeholder="{{ __('messages.admin.document_reviews_page.rejection_placeholder') }}"></textarea>
                                                <button type="submit" class="btn btn-sm btn-primary">{{ __('messages.admin.document_reviews_page.save_review') }}</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $scope === 'vehicle' ? 7 : 6 }}" class="text-center py-5 text-muted">
                                        {{ __('messages.admin.document_reviews_page.no_review_items') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if(method_exists($records, 'links'))
                <div class="card-footer bg-white">
                    {{ $records->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</main>
@endsection
