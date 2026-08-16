@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="card-title mb-0">Driver Score Appeals</h5>
            <form method="GET">
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All statuses</option>
                    @foreach(['pending','approved','rejected'] as $item)
                        <option value="{{ $item }}" @selected($status === $item)>{{ ucfirst($item) }}</option>
                    @endforeach
                </select>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Driver</th>
                            <th>Status</th>
                            <th>Related Event</th>
                            <th>Submitted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($appeals as $appeal)
                            <tr>
                                <td>#{{ $appeal->id }}</td>
                                <td>{{ trim(($appeal->driver->first_name ?? '').' '.($appeal->driver->last_name ?? '')) ?: ($appeal->driver->mobile_number ?? '-') }}</td>
                                <td>{{ ucfirst($appeal->status) }}</td>
                                <td>{{ $appeal->event->title ?? '-' }}</td>
                                <td>{{ optional($appeal->created_at)->format('d M Y H:i') }}</td>
                                <td><a href="{{ url('admin/driver-score/appeals/'.$appeal->id) }}" class="btn btn-sm btn-primary">Open</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">No appeals found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            {{ $appeals->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
