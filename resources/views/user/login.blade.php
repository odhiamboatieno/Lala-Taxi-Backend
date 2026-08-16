@extends('templatesign')

@section('main')

<style>
  .cls_signin .cls_signintext {
    background-color: #ddf2ff;
    padding: 3em;
    border-radius: 56px;
}
</style>
<div class="cls_signin">
  <div class="container">
    <div class="col-lg-12">
      <h2 class="title">{{trans('messages.header.signin-delete')}}</h2>
       @if(Auth::user()==null)
       <div class="col-lg-6">
        <div class="cls_signintext">
          <h4 >{{trans('messages.profile.rider')}}</h4>
          <p >{{trans('messages.profile.delete_rider')}}</p>
          <a href="{{ url('rider_deletion') }}">{{trans('messages.profile.driver_proceed')}} <img src="images/new/arrow-right.svg" alt="{{ __('messages.blade_static.arrow_39a531d9') }}">
          </a>
        </div>
      </div>  
      <div class="col-lg-6">
        <div class="cls_signintext">
          <h4 >{{trans('messages.profile.driver')}}</h4>
          <p >{{trans('messages.profile.delete_driver')}}</p>
          <a href="{{ url('driver_deletion') }}">{{trans('messages.profile.driver_proceed')}} <img src="images/new/arrow-right.svg" alt="{{ __('messages.blade_static.arrow_39a531d9') }}">
          </a>
        </div>
      </div>
       @endif

@stop