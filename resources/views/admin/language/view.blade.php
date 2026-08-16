@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 flex-grow-1">{{ __('messages.admin.manage_language.language') }}</h5>
                    <form method="GET" action="{{ url('admin/language') }}" class="d-flex flex-wrap gap-2 ms-auto">
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ID, name, locale, status" style="min-width: 240px;">
                        <button type="submit" class="btn btn-outline-primary">Search</button>
                        @if($search !== '')
                            <a href="{{ url('admin/language') }}" class="btn btn-light">Reset</a>
                        @endif
                    </form>
                    <div class="ms-auto">
                        <a class="btn btn-success" href="{{ url('admin/add_language') }}"><i class="bi bi-plus-circle me-1"></i>{{ __('messages.admin.manage_language.add_language') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.admin.manage_language.table.id') }}</th>
                                    <th>{{ __('messages.admin.manage_language.table.name') }}</th>
                                    <th>{{ __('messages.admin.manage_language.table.value') }}</th>
                                    <th>{{ __('messages.admin.manage_language.table.status') }}</th>
                                    <th>Default</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($languages as $language)
                                    <tr>
                                        <td>{{ $language->id }}</td>
                                        <td>{{ $language->name }}</td>
                                        <td>{{ $language->value }}</td>
                                        <td>{{ $language->status }}</td>
                                        <td>{{ $language->default_language ? 'Yes' : 'No' }}</td>
                                        <td class="text-nowrap">
                                            <a href="{{ url('admin/edit_language/'.$language->id) }}" class="btn btn-success btn-sm"><i class="bi bi-pencil-square"></i></a>
                                            <a data-href="{{ url('admin/delete_language/'.$language->id) }}" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No languages found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $languages->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
