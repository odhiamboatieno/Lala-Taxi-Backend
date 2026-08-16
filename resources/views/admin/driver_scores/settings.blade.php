@extends('admin.template')

@section('main')
<div id="layout-wrapper">
    <div class="card">
        <div class="card-header"><h5 class="card-title mb-0">Driver Score Settings</h5></div>
        <div class="card-body">
            <form method="POST" action="{{ url('admin/driver-score/settings') }}">
                @csrf
                @foreach($settings as $group => $items)
                    <h6 class="text-uppercase mt-3">{{ str_replace('_', ' ', $group) }}</h6>
                    <div class="row g-3">
                        @foreach($items as $setting)
                            <div class="col-md-6">
                                <label class="form-label">{{ $setting->label ?: $setting->key }}</label>
                                @if($setting->type === 'bool')
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="checkbox" name="{{ $setting->key }}" value="1" @checked(filter_var($setting->value, FILTER_VALIDATE_BOOLEAN))>
                                        <label class="form-check-label">{{ $setting->description }}</label>
                                    </div>
                                @else
                                    <input class="form-control" name="{{ $setting->key }}" value="{{ old($setting->key, $setting->value) }}">
                                    @if($setting->description)
                                        <small class="text-muted">{{ $setting->description }}</small>
                                    @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endforeach
                <div class="mt-4">
                    <button class="btn btn-primary">Save Settings</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
