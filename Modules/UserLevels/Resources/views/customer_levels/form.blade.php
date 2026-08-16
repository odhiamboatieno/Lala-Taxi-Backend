@extends('admin.template')

@section('title-sub', $title)
@section('pagetitle', $title)

@section('main')
<div id="layout-wrapper">
    {!! Form::open([
        'url' => $result->exists ? route('userlevels.customer-levels.update', $result) : route('userlevels.customer-levels.store'),
        'method' => $result->exists ? 'PUT' : 'POST'
    ]) !!}
        @include('userlevels::partials.form_fields', ['type' => 'rider', 'title' => $title, 'result' => $result])
    {!! Form::close() !!}
</div>
@endsection
