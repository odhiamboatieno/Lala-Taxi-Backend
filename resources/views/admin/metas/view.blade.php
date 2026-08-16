@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-3">
            <div>
                <h4 class="mb-1">{{ __('messages.admin.manage_metas.title') }}</h4>
                <div class="text-muted">Manage SEO metadata with a standard paginated table.</div>
            </div>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ url('admin/metas') }}" class="row g-3 align-items-end mb-4">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ID, URL, title, keywords">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-primary flex-grow-1">Search</button>
                    <a href="{{ url('admin/metas') }}" class="btn btn-light">Reset</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle table-nowrap">
                    <thead class="table-light">
                        <tr>
                            <th>{{ __('messages.admin.manage_metas.table.id') }}</th>
                            <th>{{ __('messages.admin.manage_metas.table.url') }}</th>
                            <th>{{ __('messages.admin.manage_metas.table.title') }}</th>
                            <th>{{ __('messages.admin.manage_metas.table.description') }}</th>
                            <th>{{ __('messages.admin.manage_metas.table.keywords') }}</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($metas as $meta)
                            <tr>
                                <td>#{{ $meta->id }}</td>
                                <td>{{ $meta->url }}</td>
                                <td>{{ $meta->title }}</td>
                                <td class="text-wrap" style="max-width: 320px;">{{ $meta->description ?: '-' }}</td>
                                <td class="text-wrap" style="max-width: 320px;">{{ $meta->keywords ?: '-' }}</td>
                                <td class="text-end">
                                    <a href="{{ url('admin/edit_meta/'.$meta->id) }}" class="btn btn-sm btn-outline-success"><i class="ri-pencil-line"></i></a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">No meta records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($metas->hasPages())
                <div class="d-flex justify-content-end mt-3">{{ $metas->links('pagination::bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
