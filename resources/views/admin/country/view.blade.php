@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 flex-grow-1">{{ __('messages.admin.manage_country.country') }}</h5>
                    <form method="GET" action="{{ url('admin/country') }}" class="d-flex flex-wrap gap-2 ms-auto">
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ID, code, country, phone" style="min-width: 240px;">
                        <button type="submit" class="btn btn-outline-primary">Search</button>
                        @if($search !== '')
                            <a href="{{ url('admin/country') }}" class="btn btn-light">Reset</a>
                        @endif
                    </form>
                    <div class="ms-auto">
                        <a class="btn btn-success" href="{{ url('admin/add_country') }}"><i class="bi bi-plus-circle me-1"></i>{{ __('messages.admin.manage_country.add_country') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.admin.manage_country.table.id') }}</th>
                                    <th>{{ __('messages.admin.manage_country.table.short_name') }}</th>
                                    <th>{{ __('messages.admin.manage_country.table.long_name') }}</th>
                                    <th>{{ __('messages.admin.manage_country.table.iso3') }}</th>
                                    <th>{{ __('messages.admin.manage_country.table.num_code') }}</th>
                                    <th>{{ __('messages.admin.manage_country.table.phone_code') }}</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($countries as $country)
                                    <tr>
                                        <td>{{ $country->id }}</td>
                                        <td>{{ $country->short_name }}</td>
                                        <td>{{ $country->long_name }}</td>
                                        <td>{{ $country->iso3 ?: '-' }}</td>
                                        <td>{{ $country->num_code ?: '-' }}</td>
                                        <td>{{ $country->phone_code }}</td>
                                        <td class="text-nowrap">
                                            <a href="{{ url('admin/edit_country/'.$country->id) }}" class="btn btn-success btn-sm"><i class="bi bi-pencil-square"></i></a>
                                            <a data-href="{{ url('admin/delete_country/'.$country->id) }}" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No countries found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $countries->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
