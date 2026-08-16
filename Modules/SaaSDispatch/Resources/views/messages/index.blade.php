@extends('admin.template')

@section('title-sub', 'Messages')
@section('pagetitle', 'Messages')

@section('main')
<div id="layout-wrapper">
    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">Start Conversation</h4></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('saasdispatch.self.messages.threads.store') }}">
                        @csrf
                        <div class="mb-3"><input class="form-control" name="subject" placeholder="Subject"></div>
                        <div class="mb-3"><textarea class="form-control" name="message" rows="5" placeholder="Write your message"></textarea></div>
                        <button class="btn btn-primary">Start Thread</button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><h4 class="mb-0">Conversation Hub</h4></div>
                <div class="card-body">
                    @forelse($threads as $thread)
                        <div class="border rounded p-3 mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <div>
                                    <div class="fw-semibold">{{ $thread->subject }}</div>
                                    <div class="text-muted">{{ ucfirst($thread->status) }} | {{ optional($thread->last_message_at)->diffForHumans() ?: 'No activity yet' }}</div>
                                </div>
                                @if($thread->green_flash)
                                    <span class="badge bg-success">Green Flash</span>
                                @endif
                            </div>
                            <div class="mb-3">
                                @foreach($thread->messages as $message)
                                    <div class="border rounded p-2 mb-2">
                                        <div class="small text-muted">{{ ucfirst($message->sender_type) }}</div>
                                        <div>{{ $message->message }}</div>
                                    </div>
                                @endforeach
                            </div>
                            <form method="POST" action="{{ route('saasdispatch.self.messages.reply', $thread) }}" class="d-flex gap-2">
                                @csrf
                                <input class="form-control" name="message" placeholder="Reply to thread">
                                <button class="btn btn-primary">Send</button>
                            </form>
                        </div>
                    @empty
                        <div class="text-muted">No conversations yet.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
