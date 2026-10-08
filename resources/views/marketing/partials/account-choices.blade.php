<section class="center-hero"><h1>{{ __('website.'.$accountAction.'_title') }}</h1><p class="site-lead">{{ __('website.'.$accountAction.'_copy') }}</p></section>
<section class="site-section account-section"><div class="site-container editorial-grid">
    @foreach(['rider' => 'map-pin', 'driver' => 'car-front', 'company' => 'building-2'] as $role => $icon)
    <a class="account-choice" href="{{ url($accountAction.'_'.$role) }}"><i data-lucide="{{ $icon }}" aria-hidden="true"></i><h2>{{ __('website.account_'.$role) }}</h2><p>{{ __('website.account_'.$role.'_copy') }}</p><span>{{ __('website.'.($accountAction === 'signin' ? 'sign_in' : 'get_started')) }} <i data-lucide="arrow-right" aria-hidden="true"></i></span></a>
    @endforeach
    @if($accountAction === 'signin')
    <a class="account-choice" href="{{ route('corporate.portal.login') }}"><i data-lucide="briefcase-business" aria-hidden="true"></i><h2>{{ __('website.account_corporate') }}</h2><p>{{ __('website.account_corporate_copy') }}</p><span>{{ __('website.sign_in') }} <i data-lucide="arrow-right" aria-hidden="true"></i></span></a>
    @endif
</div></section>
@include('marketing.partials.city')
