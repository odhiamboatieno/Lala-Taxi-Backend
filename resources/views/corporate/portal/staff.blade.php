@extends('corporate.portal.layout')
@section('title', __('corporate.portal_staff'))
@section('content')
<section class="portal-card portal-split">
    <div><h2>{{ __('corporate.portal_staff') }}</h2><p>{{ __('corporate.portal_staff_hint') }}</p><div class="portal-help"><i data-lucide="shield-check" aria-hidden="true"></i><p>{{ __('corporate.portal_role_hint') }}</p></div></div>
    @unless($account->archived_at)@include('corporate.portal.staff-form', ['staffUser'=>null, 'staffAction'=>route('corporate.portal.staff.store')])@endunless
</section>
<div class="portal-heading"><h3>{{ __('corporate.portal_access_directory') }}</h3><span class="portal-badge">{{ $staff->total() }}</span></div>
@forelse($staff as $staffUser)
<details class="portal-card portal-staff" @if(old('_portal_form') === (string) $staffUser->id) open @endif>
    <summary>
        <span class="portal-person"><span class="portal-avatar" aria-hidden="true">{{ mb_substr($staffUser->name, 0, 1) }}</span><span><strong>{{ $staffUser->name }}</strong><small>{{ $staffUser->email }}</small></span></span>
        <span class="portal-actions"><span class="portal-badge">{{ __('corporate.portal_roles.'.$staffUser->role) }}</span><span class="portal-badge {{ $staffUser->status === 'Active' ? 'portal-badge--success' : '' }}">{{ __('corporate.states.'.$staffUser->status) }}</span><i data-lucide="chevron-down" aria-hidden="true"></i></span>
    </summary>
    <div class="portal-staff-content">
        <p class="portal-help"><i data-lucide="{{ $staffUser->password_set_at ? 'circle-check' : 'mail' }}" aria-hidden="true"></i>{{ __($staffUser->password_set_at ? 'corporate.portal_ready' : 'corporate.portal_pending') }}</p>
        @unless($account->archived_at)
            @include('corporate.portal.staff-form', ['staffAction'=>route('corporate.portal.staff.update', $staffUser->id)])
            @if($staffUser->status === 'Active')<form method="POST" action="{{ route('corporate.portal.staff.invite', $staffUser->id) }}">@csrf<button class="portal-button portal-button--quiet" type="submit"><i data-lucide="mail" aria-hidden="true"></i>{{ __('corporate.portal_resend') }}</button></form>@endif
        @endunless
    </div>
</details>
@empty
    <div class="portal-card portal-empty"><span class="portal-icon"><i data-lucide="shield-check" aria-hidden="true"></i></span><strong>{{ __('corporate.portal_no_staff') }}</strong></div>
@endforelse
<div class="portal-pagination">{{ $staff->links() }}</div>
@endsection
