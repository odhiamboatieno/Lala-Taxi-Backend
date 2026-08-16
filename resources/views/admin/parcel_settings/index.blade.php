@extends('admin.template')

@section('title-sub', 'Parcel Settings')
@section('pagetitle', 'Parcel Settings')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header">
            <h4 class="mb-1">Parcel Settings</h4>
            <div class="text-muted">Adjust parcel-specific platform defaults and proof-of-delivery behavior.</div>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ url('admin/parcel_settings') }}" class="row g-4">
                @csrf
                @method('PUT')
                @foreach($settings as $key => $value)
                    <div class="col-md-6">
                        <label class="form-label">{{ ucwords(str_replace('_', ' ', $key)) }}</label>
                        @if(($types[$key] ?? '') === 'bool')
                            <select name="{{ $key }}" class="form-select">
                                <option value="1" {{ $value === 'true' ? 'selected' : '' }}>Yes</option>
                                <option value="0" {{ $value !== 'true' ? 'selected' : '' }}>No</option>
                            </select>
                        @else
                            <input type="text" name="{{ $key }}" class="form-control" value="{{ $value }}">
                        @endif
                    </div>
                @endforeach
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" name="submit" value="submit" class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
