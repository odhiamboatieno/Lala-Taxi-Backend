@extends('marketing.layout')
@section('title', __('website.driver_app_title'))
@section('main')
@include('marketing.partials.guide', ['guide' => 'driver_app', 'items' => ['radio' => 'requests', 'route' => 'trips', 'wallet' => 'earnings']])
@endsection
