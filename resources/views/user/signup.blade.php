@extends('marketing.layout')
@section('title', __('website.signup_title'))
@section('main')
@include('marketing.partials.account-choices', ['accountAction' => 'signup'])
@endsection
