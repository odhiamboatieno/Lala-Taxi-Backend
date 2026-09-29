@extends('marketing.layout')
@section('title', __('website.requirements_title'))
@section('main')
@include('marketing.partials.guide', ['guide' => 'requirements', 'items' => ['user-round' => 'identity', 'file-check-2' => 'licence', 'car-front' => 'vehicle']])
@endsection
