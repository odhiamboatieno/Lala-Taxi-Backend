@extends('marketing.auth-layout')
@section('title', trans('messages.user.vehicle_info') . ' · ' . $site_name)

@section('main')
<div id="app-wrapper" ng-controller="user">
    <section class="auth-grid">
        <div class="auth-photo">
            <img src="{{ asset('images/marketing/driver-portrait.webp') }}" alt="{{ trans('messages.user.vehicle_info') }}" width="1200" height="900" fetchpriority="high">
            <h2>{{trans('messages.user.vehicle_info')}}</h2>
        </div>

        <div class="auth-form">
            <p class="site-eyebrow">{{ trans('messages.user.vehicle_type') }}</p>
            <h1>{{trans('messages.user.vehicle_info')}}</h1>
            <p class="site-lead">{{trans('messages.user.vehicle_type')}}</p>

            {{ Form::open(array('url' => 'driver_register')) }}
            <input type="hidden" name="user_type" value="Driver">
            <input type="hidden" name="_token" value="{{ csrf_token() }}">

            <div class="site-field">
                <label for="input-email">{{trans('messages.user.vehicle_name')}}</label>
                {!! Form::text('vehicle_name', '', ['placeholder' => trans('messages.user.vehicle_name'),'id' => 'input-email','style' => 'margin:0px !important' ]) !!}
                <span class="field-error">{{ $errors->first('vehicle_name') }}</span>
            </div>

            <div class="site-field">
                <label for="input-email">{{trans('messages.user.vehicle_number')}}</label>
                {!! Form::text('vehicle_number', '', ['placeholder' => trans('messages.user.vehicle_number'),'id' => 'input-email','style' => 'margin:0px !important' ]) !!}
                <span class="field-error">{{ $errors->first('vehicle_number') }}</span>
            </div>

            <div class="site-field">
                <label for="vehicle_type">{{trans('messages.user.vehicle_type')}}</label>
                <select name="vehicle_type" id="vehicle_type">
                    <option value="" selected="">{{trans('messages.user.vehicle_type')}}</option>
                    @foreach($car_type as $key => $value)
                    <option value="{{$value->id}}" >{{ $value->car_name}}</option>
                    @endforeach
                </select>
                <span class="field-error">{{ $errors->first('vehicle_type') }}</span>
            </div>

            <button id="submit-btn" type="submit" name="step" value="car_details" class="site-btn site-btn--gold">
                <span>{{trans('messages.user.continue')}}</span>
                <i data-lucide="arrow-right" aria-hidden="true"></i>
            </button>
            {{ Form::close() }}
        </div>
    </section>
</div>
@endsection
