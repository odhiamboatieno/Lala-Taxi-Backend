@extends('marketing.layout')

@section('main')
<section class="site-section">
	<div class="site-container">
		<div class="rich-content text-wrap" id="site-content">
			{!! $content !!}
		</div>
	</div>
</section>
@endsection
@push('styles')
<style type="text/css">
	.rich-content { max-width: 860px; margin-inline: auto; }
	.rich-content img { max-width: 100%; height: auto; border-radius: var(--site-radius-field); }
</style>
@endpush
@push('scripts')
<script type="text/javascript">
$( document ).ready(function() {
 
 var base_url = '{!! url() !!}';
 var user_token = '{!! Session::get('get_token') !!}';

 if(user_token!='')
 {

  $('a[href*="'+base_url+'"]').attr('href' , 'javascript:void(0)');
 
 }

});

</script>
@endpush
