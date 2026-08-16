@extends('admin.template')

@section('title-sub', 'Parcel Trip Details')
@section('pagetitle', 'Parcel Trip Details')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-1">Parcel Trip #{{ $trip->id }}</h4>
            <div class="text-muted">Review recipient, category, task, and trip progress details.</div>
        </div>
        <div class="card-body">
            <div class="row g-4">
                <div class="col-md-6"><strong>Rider:</strong> {{ optional($trip->users)->first_name }} {{ optional($trip->users)->last_name }}</div>
                <div class="col-md-6"><strong>Driver:</strong> {{ optional($trip->driver)->first_name }} {{ optional($trip->driver)->last_name }}</div>
                <div class="col-md-6"><strong>Category:</strong> {{ optional($trip->parcelCategory)->title ?: '-' }}</div>
                <div class="col-md-6"><strong>Task:</strong> {{ optional($trip->parcelTask)->title ?: '-' }}</div>
                <div class="col-md-6"><strong>Recipient:</strong> {{ optional($trip->parcelRequest)->recipient_name ?: '-' }}</div>
                <div class="col-md-6"><strong>Recipient Phone:</strong> {{ optional($trip->parcelRequest)->recipient_phone ?: '-' }}</div>
                <div class="col-md-12"><strong>Recipient Address:</strong> {{ optional($trip->parcelRequest)->recipient_address ?: '-' }}</div>
                <div class="col-md-12"><strong>Instructions:</strong> {{ optional($trip->parcelRequest)->special_instructions ?: '-' }}</div>
                <div class="col-md-6"><strong>Status:</strong> {{ $trip->status }}</div>
                <div class="col-md-6"><strong>Date:</strong> {{ $trip->created_at }}</div>
            </div>
            <div class="mt-4">
                <a href="{{ url('admin/parcel_trips') }}" class="btn btn-light">Back</a>
            </div>
        </div>
    </div>
</div>
@endsection
