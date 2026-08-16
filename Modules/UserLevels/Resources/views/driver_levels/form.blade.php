@extends('admin.template')

@section('title-sub', $title)
@section('pagetitle', $title)

@section('main')
<div id="layout-wrapper">
    {!! Form::open([
        'url' => $result->exists ? route('userlevels.driver-levels.update', $result) : route('userlevels.driver-levels.store'),
        'method' => $result->exists ? 'PUT' : 'POST'
    ]) !!}
        @include('userlevels::partials.form_fields', ['type' => 'driver', 'title' => $title, 'result' => $result])
    {!! Form::close() !!}
</div>
@endsection
