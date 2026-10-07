@extends('admin.template')

@section('title-sub', __('corporate.accounts'))
@section('pagetitle', __('corporate.accounts'))

@section('main')
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                <tbody>
                @forelse($companies as $company)
                    <tr><td>{{ $company->name }}</td><td class="text-end"><a class="btn btn-outline-primary" href="{{ route('saasdispatch.companies.accounts.index', $company) }}">{{ __('corporate.accounts') }}</a></td></tr>
                @empty
                    <tr><td>{{ __('corporate.no_companies') }}</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $companies->links() }}
    </div>
</div>
@endsection
