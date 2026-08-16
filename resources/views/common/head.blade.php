<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" xmlns:fb="http://ogp.me/ns/fb#">
<head>
  @php
    $assetVersion = $version ?? config('app.version') ?? '1';
    $headFavicon = $favicon ?? url('favicon.ico');
  @endphp
  <title>{{ $title ?? Helpers::meta((!isset($exception)) ? Route::current()->uri() : '', 'title') }} {{ $additional_title ?? '' }}</title>

  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="{{ $headFavicon }}">

  <meta name="description" content="{{ Helpers::meta((!isset($exception)) ? Route::current()->uri() : '', 'description') }}">
  <meta name="keywords" content="{{ Helpers::meta((!isset($exception)) ? Route::current()->uri() : '', 'keywords') }}">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> 
  

  <!-- <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,300i,400,400i,600,600i,700" rel="stylesheet">  -->
  <!-- {!! Html::style('css/bootstrap.min.css') !!} -->
  {!! Html::style('css/bootstrap.css') !!}
  {!! Html::style('css/font-awesome.min.css') !!}
  {!! Html::style('css/main.css?v='.$assetVersion) !!}
  {!! Html::style('css/common.css?v='.$assetVersion) !!}
  {!! Html::style('css/common1.css?v='.$assetVersion) !!}
  {!! Html::style('css/styles.css?v='.$assetVersion) !!}
   {!! Html::style('css/styles2.css?v='.$assetVersion) !!}
  {!! Html::style('css/home.css?v='.$assetVersion) !!}
  
  {!! Html::style('css/popup.css?v='.$assetVersion) !!}

  {!! Html::style('css/jquery.bxslider.css') !!}
  {!! Html::style('css/jquery.sliderTabs.min.css') !!}
  @if (Route::current()->uri() != 'driver_payment')
  {!! Html::style('css/jquery-ui.min.css') !!} 
  @endif

  <link rel="stylesheet" type="text/css" href=" https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css">
  <!-- lalatokens -->
  <link rel="stylesheet" href="{{ url('css/theme.css') }}">
  <script src="{{ url('js/colors.js') }}"></script>
  <!-- /lalatokens -->
</head>
<body ng-app="App" class="{{ (!isset($exception)) ? (Route::current()->uri() == '/' ? 'homepage' : '') : '' }} commonpage">
