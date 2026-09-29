@extends('marketing.layout')
@section('title', trans('messages.help.help_center'))
@section('main')
<div id="site-content" ng-app="App" ng-controller="help">
  <section class="center-hero help-search-hero">
    <p class="site-eyebrow">{{ __('website.help_support') }}</p>
    <h1>{{ trans('messages.help.welcome') }}</h1>
    <form class="help-search-form">
      <div id="help-search-container" class="search-container site-field">
        <input class="search-input" type="text" name="q" autocomplete="off" maxlength="1024" value="" placeholder="{{ trans('messages.help.search_anything') }}" id="help_search">
      </div>
    </form>
  </section>

  <div class="help-nav">
    <div class="subnav">
      <div class="site-container">
        <ul class="subnav-list">
          <li>
            <a class="subnav-item" href="{{ url('help') }}" data-node-id="0" aria-selected="{{ ((@$is_subcategory != 'no' || Route::current()->uri() != 'help/topic/{id}/{category}') && (@$is_subcategory != 'no' || Route::current()->uri() != 'help/article/{id}/{question}') && Route::current()->uri() != 'help') ? 'false' : 'true' }}">
              {{ trans('messages.help.help_center') }}
            </a>
          </li>
          @if ((@$is_subcategory != 'no' || Route::current()->uri() != 'help/topic/{id}/{category}') && (@$is_subcategory != 'no' || Route::current()->uri() != 'help/article/{id}/{question}') && Route::current()->uri() != 'help')
          <li>
            <a class="subnav-item" href="#" data-node-id="0" aria-selected="true">
              {{ @$result[0]->category_name }}
            </a>
          </li>
          @endif
        </ul>
      </div>
    </div>

    <section class="site-section help-section">
      <div class="site-container">
        <div class="row help-wrap">
          <div class="col-sm-3 left-menu">
            <div class="sub_menu_header visible-xs-block">
              <h2>{{ __('messages.blade_static.help_menu_a82909ba') }}</h2>
              <i class="fa fa-bars"></i>
            </div>
            <div class="navtree">
              <ul class="sidenav-list navtree-list" id="navtree" style="display: block; {{ (Route::current()->uri() == 'help' || $is_subcategory == 'no') ? 'left: 0px;' : 'left: -300px;' }}">
                @for($i=0; $i < count(@$category); $i++) 
                <li>
                  <a href="{{ (count(@$category[$i]->subcategory)) ? 'javascript:void(0);' : url('help/topic/'.@$category[$i]->category_id.'/'.str_slug(@$category[$i]->category_name,'-')) }}" class="sidenav-item {{ (count(@$category[$i]->subcategory)) ? 'navtree-next' : '' }}" data-id="{{ @$category[$i]->category_id }}" data-name="{{ @$category[$i]->category->name }}" aria-selected="{{ ((Route::current()->uri() == 'help/topic/{id}/{category}' || Route::current()->uri() == 'help/article/{id}/{question}') && (@$category[$i]->category->id == @$result[0]->category_id)) ? 'true' : 'false' }}"> {{ @$category[$i]->category_name }}
                    <span class="visible-xs"><i class="icon icon-chevron-right"></i></span>
                  </a>
                  @if(count(@$category[$i]->subcategory))
                  <ul class="sidenav-list navtree-list" id="navtree-{{ @$category[$i]->category_id }}" style="{{ (Route::current()->uri() == 'help/topic/{id}/{category}' || Route::current()->uri() == 'help/article/{id}/{question}') ? ((@$result[0]->category_id == @$category[$i]->category->id) ? 'display:block;' : '') : '' }}">
                    <li>
                      <a href="javascript:void(0);" class="sidenav-item navtree-back" data-id="{{ @$category[$i]->category_id }}" data-name="{{ @$category[$i]->category->name }}">
                        <i class="icon icon-arrow-left"></i>
                        {{ trans('messages.help.back') }}
                      </a>
                    </li>
                    @for($j=0; $j<count(@$category[$i]->subcategory); $j++)
                    @if(@$category[$i]->subcategory_(@$category[$i]->subcategory[$j]->id)->count())
                    <li>
                      <a href="{{ url('help/topic/'.@$category[$i]->subcategory[$j]->id.'/'.str_slug(@$category[$i]->subcategory[$j]->name,'-')) }}" class="sidenav-item" aria-selected="{{ (@$result[0]->subcategory_id == @$category[$i]->subcategory[$j]->id && Route::current()->uri() != 'help') ? 'true' : 'false' }}">{{ @$category[$i]->subcategory[$j]->name_lang }}
                        <span class="visible-xs"><i class="icon icon-chevron-right"></i></span>
                      </a>
                    </li>
                    @endif
                    @endfor
                  </ul>
                  @endif
                </li>
                @endfor
              </ul>
            </div>
          </div>

          @if (Route::current()->uri() == 'help/topic/{id}/{category}')
          <div class="col-xs-12 col-xs-offset-0 col-sm-8 col-sm-offset-1 help-content text-copy navtree-content breadcrumbs-content">
            <h2>
              {{ (@$subcategory_count == 0) ? @$result[0]->category_name : @$result[0]->subcategory_name }}
            </h2>

            <div class="help-article-list">
              @foreach($result as $row)
              <a href="{{ url('help/article/'.$row->id.'/'.str_slug($row->question_lang,'-')) }}" class="article-link link-reset article-link-panel help-article">
                <i data-lucide="help-circle" aria-hidden="true"></i>
                <span class="article-link-right">{{ str_replace('SITE_NAME', $site_name, $row->question_lang) }}</span>
              </a>
              @endforeach
            </div>
          </div>
          @elseif (Route::current()->uri() == 'help/article/{id}/{question}')

          <div class="col-xs-12 col-xs-offset-0 col-sm-8 col-sm-offset-1 help-content text-copy navtree-content breadcrumbs-content">
            <div class="help-center-sm">
              <div class="space-8">
                <h2>{{ str_replace('SITE_NAME', $site_name, $result[0]->question_lang) }}</h2>
                <div class="text-copy space-8 img-alignment rich-content">
                  {!! str_replace('SITE_NAME', $site_name, $result[0]->answer_lang) !!}
                </div>
              </div>
            </div>
          </div>

          @else
          <div class="col-md-8 col-offset-1 help-content text-copy navtree-content breadcrumbs-content">
            <div class="popular-topics">
              <div class="row row-space-6">
                <h2>{{ trans('messages.help.suggested_helps') }}</h2>
                <div class="homepage-articles-list help-article-list">
                  @foreach($result as $row)
                  <a href="{{ url('help/article/'.$row->id.'/'.str_slug($row->question,'-')) }}" class="article-link homepage-article-link-panel link-reset help-article">
                    <i data-lucide="help-circle" aria-hidden="true"></i>
                    <span class="article-link-right">{{ str_replace('SITE_NAME', $site_name, $row->question_lang) }}</span>
                   </a>
                  @endforeach
                  @if($result->count() == 0)
                  {{ trans('messages.help.no_suggested_helps') }}
                  @endif
                </div>
              </div>
            </div>
          </div>
          @endif
        </div>
      </div>
    </section>
  </div>
</div>
@endsection

@push('styles')
@include('marketing.partials.legacy-styles')
<style type="text/css">
  .help-search-hero .help-search-form { max-width: 640px; margin: 28px auto 0; }
  .help-search-hero .site-field { margin-bottom: 0; }
  .help-nav .subnav { border-bottom: 1px solid var(--site-line); }
  .help-nav .subnav-list { list-style: none; display: flex; flex-wrap: wrap; gap: 8px 28px; margin: 0; padding: 14px 0; }
  .help-nav .subnav-item { font-weight: 600; }
  .help-nav .subnav-item[aria-selected="true"] { text-decoration: underline; text-decoration-color: var(--site-gold); text-underline-offset: 6px; text-decoration-thickness: 3px; }
  .help-section { padding-top: 48px; }
  .help-wrap { display: flex; flex-wrap: wrap; }
  .help-wrap .left-menu { margin-bottom: 32px; }
  .left-menu .navtree { border: 1px solid var(--site-line); border-radius: var(--site-radius-image); padding: 14px 20px; }
  .left-menu .sidenav-list { list-style: none; margin: 0; padding: 0; }
  .left-menu .sidenav-item { display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 11px 0; font-weight: 600; }
  .left-menu .sidenav-item[aria-selected="true"] { color: var(--site-ink); text-decoration: underline; text-underline-offset: 5px; }
  .left-menu .navtree-back { color: var(--site-muted); font-weight: 550; gap: 8px; justify-content: flex-start; }
  .help-article-list { display: grid; gap: 14px; margin-top: 24px; }
  .help-article { display: flex; align-items: center; gap: 14px; padding: 18px 22px; border: 1px solid var(--site-line); border-radius: var(--site-radius-field); font-weight: 600; }
  .help-article:hover { border-color: var(--site-ink); }
  .help-content h2 { margin-bottom: 8px; }
  .help-content .rich-content { margin-top: 20px; }
</style>
@endpush

@push('scripts')
<script type="text/javascript">
  var APP_URL = {!! json_encode(url('/')) !!};
</script>
<script src="{{ asset('js/jquery-1.11.3.js') }}"></script>
<script src="{{ asset('js/jquery-ui.js') }}"></script>
<script src="{{ asset('js/angular.js') }}"></script>
<script src="{{ asset('js/angular-sanitize.js') }}"></script>
<script type="text/javascript">
  var app = angular.module('App', ['ngSanitize']);
</script>
<script src="{{ asset('js/common.js') }}"></script>
@if (Route::current()->uri() != 'help')
<script type="text/javascript">
  $(document).ready(function() {
    $('.navtree > .sidenav-list.navtree-list').addClass('active');
  });
</script>
@endif
@endpush
