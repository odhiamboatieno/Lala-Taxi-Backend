@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div class="flex-grow-1">
                        <h5 class="card-title mb-1">Issue #{{ $issue->id }} · {{ $issue->subject }}</h5>
                        <div class="text-muted">
                            {{ ucfirst($issue->type) }} · {{ $issue->category }} · {{ ucwords(str_replace('_', ' ', $issue->status)) }}
                        </div>
                    </div>
                    <a href="{{ url('admin/support_issues') }}" class="btn btn-light btn-sm">Back</a>
                </div>
                <div class="card-body">
                    <div class="mb-4">
                        <strong>Rider:</strong>
                        {{ trim(($issue->rider->first_name ?? '') . ' ' . ($issue->rider->last_name ?? '')) ?: '-' }}
                        <span class="text-muted">({{ $issue->rider->mobile_number ?? $issue->rider->email ?? '-' }})</span>
                    </div>
                    @if($issue->trip_id)
                        <div class="mb-4">
                            <strong>Trip:</strong>
                            <a href="{{ url('admin/trips/'.$issue->trip_id) }}">#{{ $issue->trip_id }}</a>
                        </div>
                    @endif
                    <div class="border rounded p-3 mb-4 bg-light">
                        {{ $issue->description ?: 'No description provided.' }}
                    </div>
                    <div class="d-flex flex-column gap-3">
                        @foreach($issue->messages as $message)
                            <div class="border rounded p-3 {{ $message->sender_type === 'admin' ? 'bg-primary-subtle ms-5' : 'bg-light me-5' }}">
                                <div class="fw-semibold mb-1">{{ ucfirst($message->sender_type) }}</div>
                                <div>{{ $message->message }}</div>
                                <div class="text-muted small mt-2">{{ optional($message->created_at)->format('d M Y, h:i A') }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Respond</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ url('admin/support_issues/'.$issue->id.'/reply') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                @foreach(['open','in_progress','waiting_rider','resolved','closed'] as $item)
                                    <option value="{{ $item }}" @selected($issue->status === $item)>{{ ucwords(str_replace('_', ' ', $item)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Message</label>
                            <textarea name="message" class="form-control" rows="6" required>{{ old('message') }}</textarea>
                        </div>
                        <button class="btn btn-primary w-100" type="submit">Send reply</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
