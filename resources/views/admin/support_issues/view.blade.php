@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 flex-grow-1">Rider Support Issues</h5>
                    <form method="GET" action="{{ url('admin/support_issues') }}" class="d-flex gap-2">
                        <select name="status" class="form-select form-select-sm">
                            <option value="">All statuses</option>
                            @foreach(['open','in_progress','waiting_rider','resolved','closed'] as $item)
                                <option value="{{ $item }}" @selected($status === $item)>{{ ucwords(str_replace('_', ' ', $item)) }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-primary" type="submit">Filter</button>
                    </form>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Rider</th>
                                    <th>Trip</th>
                                    <th>Type</th>
                                    <th>Category</th>
                                    <th>Subject</th>
                                    <th>Status</th>
                                    <th>Last update</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($issues as $issue)
                                    <tr>
                                        <td>#{{ $issue->id }}</td>
                                        <td>
                                            {{ trim(($issue->rider->first_name ?? '') . ' ' . ($issue->rider->last_name ?? '')) ?: ($issue->rider->mobile_number ?? '-') }}
                                        </td>
                                        <td>{{ $issue->trip_id ? '#'.$issue->trip_id : '-' }}</td>
                                        <td>{{ ucfirst($issue->type) }}</td>
                                        <td>{{ $issue->category }}</td>
                                        <td>{{ $issue->subject }}</td>
                                        <td><span class="badge bg-primary">{{ ucwords(str_replace('_', ' ', $issue->status)) }}</span></td>
                                        <td>{{ optional($issue->last_message_at ?: $issue->updated_at)->format('d M Y, h:i A') }}</td>
                                        <td><a class="btn btn-sm btn-primary" href="{{ url('admin/support_issues/'.$issue->id) }}">Open</a></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center text-muted">No rider issues found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $issues->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
