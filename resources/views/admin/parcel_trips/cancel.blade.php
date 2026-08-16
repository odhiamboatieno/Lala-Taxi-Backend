@extends('admin.template')

@section('title-sub', 'Cancel Parcel Trip')
@section('pagetitle', 'Cancel Parcel Trip')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-1">Cancel Parcel Trip #{{ $trip->id }}</h4>
            <div class="text-muted">Use this only when the parcel delivery should be force-cancelled from the dashboard.</div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('admin/cancel_parcel_trip/'.$trip->id) }}" class="row g-3">
                @csrf
                <div class="col-12">
                    <label class="form-label">Reason</label>
                    <textarea name="cancel_reason" class="form-control" rows="4" placeholder="Enter cancellation reason"></textarea>
                </div>
                <div class="col-12 d-flex justify-content-end gap-2">
                    <a href="{{ url('admin/parcel_trips') }}" class="btn btn-light">Back</a>
                    <button type="submit" name="submit" value="submit" class="btn btn-danger">Cancel Trip</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
