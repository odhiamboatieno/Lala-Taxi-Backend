@if($errors->any())
<div class="alert alert-danger" role="alert"><p class="fw-semibold mb-2">{{ __('corporate.correct_errors') }}</p><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif
