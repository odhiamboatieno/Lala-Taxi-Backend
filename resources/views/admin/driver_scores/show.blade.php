@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Driver Score Summary</h5></div>
                <div class="card-body">
                    <h2 class="mb-1">{{ number_format($score->score_percentage, 2) }}%</h2>
                    <p class="mb-2"><strong>Status:</strong> {{ $score->status }}</p>
                    <p class="mb-2"><strong>Marketplace:</strong> {{ ucwords(str_replace('_',' ',$score->marketplace_access_status)) }}</p>
                    <p class="mb-2"><strong>Completed trips:</strong> {{ $score->total_completed_trips }}</p>
                    <p class="mb-2"><strong>Acceptance:</strong> {{ number_format($score->acceptance_rate, 2) }}%</p>
                    <p class="mb-2"><strong>Completion:</strong> {{ number_format($score->completion_rate, 2) }}%</p>
                    <p class="mb-2"><strong>Average rating:</strong> {{ number_format($score->average_rating, 2) }}</p>
                    <p class="mb-0"><strong>Next calculation:</strong> {{ optional($score->next_calculation_at)->format('d M Y H:i') ?: '-' }}</p>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header"><h5 class="card-title mb-0">Manual Marketplace Access Override</h5></div>
                <div class="card-body">
                    <form method="POST" action="{{ url('admin/driver-score/'.$driver->id.'/override-marketplace') }}">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select class="form-select" name="marketplace_access_status">
                                @foreach(['active','limited','review_required','suspended'] as $status)
                                    <option value="{{ $status }}" @selected($score->marketplace_access_status === $status)>{{ ucwords(str_replace('_',' ',$status)) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" value="1" id="override_active" name="override_active" @checked(($score->metadata['manual_access_override_active'] ?? false) === true)>
                            <label class="form-check-label" for="override_active">Keep this override active during recalculation</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Audit note</label>
                            <textarea class="form-control" rows="3" name="note">{{ old('note') }}</textarea>
                        </div>
                        <button class="btn btn-primary">Save Override</button>
                        <a href="{{ url('admin/driver-score/'.$driver->id.'/recalculate') }}" class="btn btn-outline-primary">Recalculate Now</a>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Breakdown</h5></div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach(($payload['breakdown_percentages'] ?? []) as $factor)
                            <div class="col-md-6">
                                <div class="border rounded p-3 h-100">
                                    <div class="d-flex justify-content-between">
                                        <strong>{{ $factor['label'] }}</strong>
                                        <span>{{ number_format($factor['score'], 2) }}%</span>
                                    </div>
                                    <small class="text-muted d-block mt-1">Weight: {{ number_format($factor['weight'], 2) }}</small>
                                    <small class="text-muted d-block mt-2">{{ $factor['detail'] }}</small>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
            <div class="card mt-3">
                <div class="card-header"><h5 class="card-title mb-0">Recent Score Events</h5></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Event</th>
                                    <th>Impact</th>
                                    <th>Scoring</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($events as $event)
                                    <tr>
                                        <td>{{ optional($event->event_date ?: $event->created_at)->format('d M Y H:i') }}</td>
                                        <td>
                                            <strong>{{ $event->title }}</strong>
                                            <div class="text-muted small">{{ $event->description }}</div>
                                        </td>
                                        <td>{{ number_format($event->impact_score, 2) }}</td>
                                        <td>{{ $event->excluded_from_scoring ? 'Excluded' : 'Active' }}</td>
                                        <td>
                                            @if($event->excluded_from_scoring)
                                                <form method="POST" action="{{ url('admin/driver-score/events/'.$event->id.'/restore') }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-primary">Restore impact</button>
                                                </form>
                                            @else
                                                <form method="POST" action="{{ url('admin/driver-score/events/'.$event->id.'/exclude') }}">
                                                    @csrf
                                                    <button class="btn btn-sm btn-outline-danger">Exclude</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center text-muted">No score events found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $events->links('pagination::bootstrap-5') }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
