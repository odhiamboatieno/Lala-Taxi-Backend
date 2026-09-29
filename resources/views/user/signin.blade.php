@extends('marketing.layout')
@section('title', __('website.signin_title'))
@section('main')
@include('marketing.partials.account-choices', ['accountAction' => 'signin'])
@endsection
