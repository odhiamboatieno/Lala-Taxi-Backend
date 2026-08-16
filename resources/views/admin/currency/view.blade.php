@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 flex-grow-1">{{ __('messages.admin.manage_currency.currency') }}</h5>
                    <form method="GET" action="{{ url('admin/currency') }}" class="d-flex flex-wrap gap-2 ms-auto">
                        <input type="text" name="search" class="form-control" value="{{ $search }}" placeholder="ID, name, code, status" style="min-width: 240px;">
                        <button type="submit" class="btn btn-outline-primary">Search</button>
                        @if($search !== '')
                            <a href="{{ url('admin/currency') }}" class="btn btn-light">Reset</a>
                        @endif
                    </form>
                    <div class="ms-auto">
                        <a class="btn btn-success" href="{{ url('admin/add_currency') }}"><i class="bi bi-plus-circle me-1"></i>{{ __('messages.admin.manage_currency.add_currency') }}</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-nowrap table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>{{ __('messages.admin.manage_currency.table.id') }}</th>
                                    <th>{{ __('messages.admin.manage_currency.table.name') }}</th>
                                    <th>{{ __('messages.admin.manage_currency.table.code') }}</th>
                                    <th>Symbol</th>
                                    <th>{{ __('messages.admin.manage_currency.table.rate') }}</th>
                                    <th>{{ __('messages.admin.manage_currency.table.status') }}</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($currencies as $currency)
                                    <tr>
                                        <td>{{ $currency->id }}</td>
                                        <td>{{ $currency->name }}</td>
                                        <td>{{ $currency->code }}</td>
                                        <td>{{ $currency->symbol }}</td>
                                        <td>{{ $currency->rate }}</td>
                                        <td>{{ $currency->status }}</td>
                                        <td class="text-nowrap">
                                            <a href="{{ url('admin/edit_currency/'.$currency->id) }}" class="btn btn-success btn-sm"><i class="bi bi-pencil-square"></i></a>
                                            <a data-href="{{ url('admin/delete_currency/'.$currency->id) }}" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-delete"><i class="bi bi-trash3"></i></a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">No currencies found.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $currencies->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
