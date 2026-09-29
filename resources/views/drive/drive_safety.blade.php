@extends('marketing.layout')
@section('title', __('website.driver_safety_title'))
@section('main')
@include('marketing.partials.guide', ['guide' => 'driver_safety', 'items' => ['shield-check' => 'prepare', 'map-pin' => 'pickup', 'circle-help' => 'support']])
@endsection
