 @extends('template')
 
   @section('main')
    
    <main id="site-content" role="main">
      
<div class="container" style="min-height: 485px">
  <div class="row " style="margin:3em 0">
    <div class="col-md-12 text-center">
      <h1 class="text-jumbo text-ginormous hide-sm">{{trans('messages.errors.unauthorize')}}</h1>
      <!-- <h1 class="text-jumbo text-ginormous hide-sm">{{ __('messages.blade_static.coming_soon_fbe75d95') }}</h1> -->
      <!-- <h2></h2> -->
      <!-- <h2>{{ __('messages.blade_static.we_are_working_on_this_page_will_update_it_soon_07f44967') }}</h2> -->
      <h4>{{trans('messages.errors.content_block')}}</h4>
    </div>
  </div>
</div>

    </main>

@stop
<style type="text/css">
  .row.row-space-top-8.row-space-8{
    margin-top: 230px !important;
    margin-bottom: 250px !important;
  }
</style>