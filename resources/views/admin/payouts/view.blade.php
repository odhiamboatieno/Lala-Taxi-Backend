@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1">{{ __('messages.admin.manage_payouts.driver_payouts_title', ['payout_title' => $payout_title]) }}</h4>
                <div class="text-muted">Review grouped driver payout records without DataTables.</div>
            </div>
            <div class="text-muted">Total rows : <strong>{{ $rows->total() }}</strong></div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ request()->url() }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="Driver ID or name">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ request()->url() }}" class="btn btn-light">Reset</a>
                </div>
            </form>
            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>Driver ID</th>
                            @if($filter_type === 'Weekly')
                                <th>Week Day</th>
                            @else
                                <th>Driver Name</th>
                            @endif
                            <th>Payout Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rows as $row)
                            <tr>
                                <td>#{{ $row->driver_id }}</td>
                                @if($filter_type === 'Weekly')
                                    <td>{{ $row->week_day }}</td>
                                @else
                                    <td>{{ $row->driver_name }}</td>
                                @endif
                                <td>{{ $row->payout_amount }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center py-5 text-muted">No payout records found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($rows->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $rows->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
