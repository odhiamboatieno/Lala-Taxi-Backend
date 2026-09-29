@extends('admin.template')
@section('pagetitle', 'Admin component preview')
@section('title-sub', 'Design review')
@section('main')
{{-- Synthetic data for local regression checks. Not registered as a production route. --}}
<div class="alert alert-warning">Read-only design preview. Sample records, no live account actions.</div>
<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between"><h5 class="card-title mb-0">Drivers</h5><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#preview-modal">Review details</button></div>
    <div class="card-body">
        <form class="row g-3 align-items-end" onsubmit="return false">
            <div class="col-md-5"><label class="form-label" for="filter-name">Search drivers</label><input class="form-control" id="filter-name" placeholder="Name or phone"></div>
            <div class="col-md-4"><label class="form-label" for="filter-status">Status</label><select class="form-select" id="filter-status"><option>All statuses</option><option>Active</option><option>Pending</option></select></div>
            <div class="col-md-3"><button class="btn btn-primary" id="apply-filter" type="button">Apply filter</button></div>
        </form>
        <div class="table-responsive mt-4"><table class="table table-hover" id="preview-table"><thead><tr><th>Name</th><th>Vehicle</th><th>Status</th><th>Trips</th><th>Action</th></tr></thead><tbody>
        @foreach([['James Mwangi','KDG 472K','Active',124],['Mary Wanjiku','KDJ 824M','Pending',87],['David Otieno','KDA 581H','Active',205],['Grace Achieng','KDH 482P','Active',92]] as $row)
            <tr><td>{{ $row[0] }}</td><td>{{ $row[1] }}</td><td><span class="badge {{ $row[2] === 'Active' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">{{ $row[2] }}</span></td><td>{{ $row[3] }}</td><td><div class="dropdown"><button class="btn btn-sm btn-outline-secondary" data-bs-toggle="dropdown">Actions</button><ul class="dropdown-menu"><li><button class="dropdown-item" data-bs-toggle="modal" data-bs-target="#preview-modal">View details</button></li></ul></div></td></tr>
        @endforeach
        </tbody></table></div>
    </div>
</div>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Form states</h5></div><div class="card-body"><div class="row g-4">
    <div class="col-md-4"><label class="form-label" for="normal">Default</label><input class="form-control" id="normal" value="Editable field"></div>
    <div class="col-md-4"><label class="form-label" for="invalid">Validation error</label><input class="form-control is-invalid" id="invalid" aria-invalid="true" aria-describedby="invalid-help"><div class="invalid-feedback" id="invalid-help">Please enter a valid value.</div></div>
    <div class="col-md-4"><label class="form-label" for="disabled">Disabled</label><input class="form-control" id="disabled" value="Not editable" disabled></div>
    <div class="col-12"><label class="form-check"><input class="form-check-input" type="checkbox" checked> Checkbox</label><label class="form-check"><input class="form-check-input" type="radio" checked> Radio button</label></div>
    <div class="col-12 d-flex flex-wrap gap-2"><button class="btn btn-primary">Primary</button><button class="btn btn-outline-secondary">Secondary</button><button class="btn btn-danger">Destructive</button><button class="btn btn-primary" disabled>Disabled</button><img class="rounded-circle" src="{{ asset('assets/images/avatar/dummy-avatar.jpg') }}" width="40" height="40" alt="Avatar shape test"></div>
</div></div></div>
<div class="modal fade" id="preview-modal" tabindex="-1" aria-labelledby="preview-modal-title" aria-hidden="true"><div class="modal-dialog"><div class="modal-content"><div class="modal-header"><h5 class="modal-title" id="preview-modal-title">Driver details</h5><button class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body">Sample driver details for design verification.</div><div class="modal-footer"><button class="btn btn-light" data-bs-dismiss="modal">Close</button></div></div></div></div>
@endsection
@section('js')
<script src="{{ asset('admin_assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('admin_assets/plugins/datatables/dataTables.bootstrap.min.js') }}"></script>
<script>$(function(){const table=$('#preview-table').DataTable({pageLength:2,lengthMenu:[2,4],autoWidth:false});$('#apply-filter').on('click',function(){table.search($('#filter-name').val()).draw();});});</script>
@endsection
