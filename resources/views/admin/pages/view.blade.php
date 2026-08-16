@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center flex-wrap gap-2">
                    <h5 class="card-title mb-0 flex-grow-1">{{ __('messages.admin.manage_static_pages.pages') }}</h5>
                    @if(Auth::guard('admin')->user()->can('manage_static_pages'))
                        <div class="ms-auto">
                            <a class="btn btn-success" href="{{ url('admin/add_page') }}"><i class="bi bi-plus-circle me-1"></i>{{ __('messages.admin.manage_static_pages.add_page') }}</a>
                        </div>
                    @endif
                </div>
                <div class="card-body">
                    {!! $dataTable->table(['class' => 'table table-nowrap table-striped table-bordered w-100', 'id' => 'pages-table']) !!}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ url('js/dataTables.buttons.js') }}"></script>
    <script src="{{ url('js/buttons.server-side.js') }}"></script>
    {!! $dataTable->scripts() !!}
@endpush
