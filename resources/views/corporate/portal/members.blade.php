@extends('corporate.portal.layout')
@section('title', __('corporate.members'))
@section('content')
<section class="portal-card portal-split">
    <div><h2>{{ __('corporate.members') }}</h2><p>{{ __('corporate.portal_member_hint') }}</p><div class="portal-help"><i data-lucide="smartphone" aria-hidden="true"></i><p>{{ __('corporate.portal_member_registration_hint') }}</p></div></div>
    @unless($account->archived_at)
    <form class="portal-form portal-form--grid" method="POST" action="{{ route('corporate.portal.members.store') }}">
        @csrf
        <label for="country_code">{{ __('corporate.portal_country_code') }}<input id="country_code" class="form-control" type="text" name="country_code" inputmode="numeric" pattern="[0-9]{1,4}" value="{{ old('country_code') }}" maxlength="4" required><small>{{ __('corporate.portal_country_hint') }}</small></label>
        <label for="mobile_number">{{ __('corporate.portal_mobile_number') }}<input id="mobile_number" class="form-control" type="tel" name="mobile_number" inputmode="numeric" pattern="[0-9]{5,20}" value="{{ old('mobile_number') }}" maxlength="20" required></label>
        <label for="role">{{ __('corporate.role') }}<select id="role" class="form-select" name="role">@foreach(['member','manager','finance'] as $role)<option value="{{ $role }}" @selected(old('role','member') === $role)>{{ __('corporate.roles.'.$role) }}</option>@endforeach</select></label>
        <div class="portal-form-footer"><button class="portal-button portal-button--gold" type="submit"><i data-lucide="user-round-plus" aria-hidden="true"></i>{{ __('corporate.portal_add_member') }}</button></div>
    </form>
    @endunless
</section>
<section class="portal-card">
    <div class="portal-heading"><h3>{{ __('corporate.portal_employee_directory') }}</h3><span class="portal-badge">{{ $members->total() }}</span></div>
    @if($members->isNotEmpty())<p class="portal-table-hint"><i data-lucide="move-horizontal" aria-hidden="true"></i>{{ __('corporate.portal_table_hint') }}</p>@endif
    <div class="portal-table-wrap" tabindex="0" role="region" aria-label="{{ __('corporate.portal_employee_directory') }}"><table>
        <thead><tr><th scope="col">{{ __('corporate.portal_name') }}</th><th scope="col">{{ __('corporate.portal_mobile_number') }}</th><th scope="col">{{ __('corporate.role') }}</th><th scope="col">{{ __('corporate.status') }}</th>@unless($account->archived_at)<th scope="col">{{ __('corporate.actions') }}</th>@endunless</tr></thead>
        <tbody>@forelse($members as $member)<tr>
            <td>{{ $member->first_name }} {{ $member->last_name }}</td><td>+{{ $member->country_code }} {{ $member->mobile_number }}</td><td>{{ __('corporate.roles.'.$member->corporate_role) }}</td><td><span class="portal-badge {{ $member->status === 'Active' ? 'portal-badge--success' : '' }}">{{ __('corporate.states.'.$member->status) }}</span></td>
            @unless($account->archived_at)<td><form method="POST" action="{{ route('corporate.portal.members.remove', $member->id) }}">@csrf @method('DELETE')<button class="portal-button portal-button--danger" type="submit" data-confirm="{{ __('corporate.portal_remove_confirm') }}">{{ __('corporate.remove_member') }}</button></form></td>@endunless
        </tr>@empty<tr><td colspan="{{ $account->archived_at ? 4 : 5 }}"><div class="portal-empty"><span class="portal-icon"><i data-lucide="users-round" aria-hidden="true"></i></span><strong>{{ __('corporate.no_members') }}</strong><p>{{ __('corporate.portal_member_registration_hint') }}</p></div></td></tr>@endforelse</tbody>
    </table></div>
    <div class="portal-pagination">{{ $members->links() }}</div>
</section>
@endsection
