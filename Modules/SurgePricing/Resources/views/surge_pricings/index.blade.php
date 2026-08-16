@extends('admin.template')

@section('main')
<div class="content-wrapper">
    <section class="content-header"><h1>Surge Price Setup</h1></section>
    <section class="content">
        <div class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <form method="get" class="d-flex gap-2">
                        <input type="text" name="search" value="{{ $search }}" class="form-control" placeholder="Search by name">
                        <button class="btn btn-success">Search</button>
                    </form>
                    <a href="{{ url($baseUrl.'/create') }}" class="btn btn-primary">Create Surge Price</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Surge Info</th>
                                <th>Zone</th>
                                <th>Extra Price (%)</th>
                                <th>Time & Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($surgePricings as $index => $surgePricing)
                            <tr>
                                <td>{{ $surgePricings->firstItem() + $index }}</td>
                                <td>{{ $surgePricing->name }}<br><small>#{{ $surgePricing->id }}</small></td>
                                <td>{{ $surgePricing->zone_scope === 'all' ? 'All zones' : $surgePricing->locations->pluck('name')->implode(', ') }}</td>
                                <td>{{ $surgePricing->ride_rate }}% ride / {{ $surgePricing->parcel_rate }}% parcel</td>
                                <td>{{ optional($surgePricing->range_start)->format('d M y') }} - {{ optional($surgePricing->range_end)->format('d M y') }}</td>
                                <td>{{ $surgePricing->status ? 'Active' : 'Inactive' }}</td>
                                <td class="text-nowrap">
                                    <a href="{{ url($baseUrl.'/'.$surgePricing->id) }}" class="btn btn-sm btn-success">View</a>
                                    <a href="{{ url($baseUrl.'/'.$surgePricing->id.'/edit') }}" class="btn btn-sm btn-info">Edit</a>
                                    <form action="{{ url($baseUrl.'/'.$surgePricing->id.'/toggle') }}" method="post" style="display:inline">@csrf<button class="btn btn-sm btn-warning">Toggle</button></form>
                                    <form action="{{ url($baseUrl.'/'.$surgePricing->id) }}" method="post" style="display:inline">@csrf @method('DELETE')<button onclick="return confirm('Delete this surge price?')" class="btn btn-sm btn-danger">Delete</button></form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center">No surge pricing records found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                {{ $surgePricings->links() }}
            </div>
        </div>
    </section>
</div>
@endsection
