@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row g-3">
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Appeal Details</h5></div>
                <div class="card-body">
                    <p><strong>Driver:</strong> {{ trim(($appeal->driver->first_name ?? '').' '.($appeal->driver->last_name ?? '')) ?: ($appeal->driver->mobile_number ?? '-') }}</p>
                    <p><strong>Status:</strong> {{ ucfirst($appeal->status) }}</p>
                    <p><strong>Created:</strong> {{ optional($appeal->created_at)->format('d M Y H:i') }}</p>
                    <p><strong>Reason:</strong></p>
                    <div class="border rounded p-3 bg-light">{{ $appeal->reason }}</div>
                    @if($appeal->event)
                        <hr>
                        <p><strong>Related Event:</strong> {{ $appeal->event->title }}</p>
                        <p class="mb-0 text-muted">{{ $appeal->event->description }}</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Review Appeal</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ url('admin/driver-score/appeals/'.$appeal->id) }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Decision</label>
                            <select name="status" class="form-select">
                                <option value="approved">Approve</option>
                                <option value="rejected">Reject</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Admin Notes</label>
                            <textarea name="admin_notes" rows="5" class="form-control">{{ old('admin_notes', $appeal->admin_notes) }}</textarea>
                        </div>
                        @if($appeal->event)
                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="1" id="restore_event_impact" name="restore_event_impact">
                                <label class="form-check-label" for="restore_event_impact">Exclude the related event from scoring</label>
                            </div>
                        @endif
                        <button class="btn btn-primary">Submit Review</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
