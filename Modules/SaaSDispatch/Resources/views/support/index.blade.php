@extends('admin.template')

@section('title-sub', 'Support & System Status')
@section('pagetitle', 'Support & System Status')

@section('main')
<div id="layout-wrapper">
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">Open Ticket</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('saasdispatch.self.support.store') }}">
                        @csrf
                        <div class="mb-3"><input class="form-control" name="subject" placeholder="Ticket subject"></div>
                        <div class="mb-3">
                            <select class="form-control" name="category">
                                <option value="general">General</option>
                                <option value="billing">Billing</option>
                                <option value="dispatch">Dispatch</option>
                                <option value="technical">Technical</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <select class="form-control" name="priority">
                                <option value="low">Low</option>
                                <option value="normal" selected>Normal</option>
                                <option value="high">High</option>
                                <option value="urgent">Urgent</option>
                            </select>
                        </div>
                        <div class="mb-3"><textarea class="form-control" name="description" rows="6" placeholder="Describe the issue"></textarea></div>
                        <button class="btn btn-primary">Create Ticket</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">Tickets</h4>
                    <span class="badge bg-info">Platform Status: Operational</span>
                </div>
                <div class="card-body">
                    @forelse($tickets as $ticket)
                        <div class="border rounded p-3 mb-4" id="search-saas_support_tickets-{{ $ticket->id }}">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="fw-semibold">{{ $ticket->subject }}</div>
                                    <div class="text-muted">{{ ucfirst($ticket->category) }} | {{ ucfirst($ticket->priority) }} | {{ ucfirst($ticket->status) }}</div>
                                </div>
                                <span class="badge bg-secondary">#{{ $ticket->id }}</span>
                            </div>
                            <div class="mb-3">
                                @foreach($ticket->messages as $message)
                                    <div class="border rounded p-2 mb-2">
                                        <div class="small text-muted">{{ ucfirst($message->sender_type) }}</div>
                                        <div>{{ $message->message }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <form method="POST" action="{{ route('saasdispatch.self.support.reply', $ticket) }}" class="row g-2">
                                @csrf
                                <div class="col-md-8"><input class="form-control" name="message" placeholder="Reply to ticket"></div>
                                <div class="col-md-2">
                                    <select class="form-control" name="status">
                                        <option value="">Keep status</option>
                                        <option value="open">Open</option>
                                        <option value="pending">Pending</option>
                                        <option value="resolved">Resolved</option>
                                        <option value="closed">Closed</option>
                                    </select>
                                </div>
                                <div class="col-md-2"><button class="btn btn-primary w-100">Update</button></div>
                            </form>
                        </div>
                    @empty
                        <div class="text-muted">No support tickets created yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
