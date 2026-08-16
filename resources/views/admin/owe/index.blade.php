@extends('admin.template')
@section('main')
    @include('admin.partials.datatable-card', [
        'title' => __('messages.admin.manage_owe_amounts.title', ['title' => $main_title]),
        'addButton' => isset($total_owe_amount)
            ? '<div class="d-flex flex-wrap gap-2 justify-content-end"><a class="btn btn-success" href="'.route('owe_details', ['type' => 'overall']).'">Total Owe Amount : '.$currency_code.$total_owe_amount.'</a><span class="btn btn-warning disabled">Remaining Owe Amount : '.$currency_code.$remaining_owe_amount.'</span></div>'
            : '',
        'bodyHeader' => isset($total_owe_amount) ? '<div class="mb-3 fw-semibold">'.$sub_title.'</div>' : '',
        'table' => $dataTable->table(['class' => 'table table-nowrap table-striped table-bordered w-100', 'id' => 'owe-table']),
    ])
@endsection
@push('scripts')
    <style>
        .min_width {
            width: 200px;
            overflow: hidden;
            word-wrap: break-word;
        }
    </style>
    <script src="{{ url('js/dataTables.buttons.js') }}"></script>
    <script src="{{ url('js/buttons.server-side.js') }}"></script>
    {!! $dataTable->scripts() !!}
@endpush
