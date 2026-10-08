@if($trips->isNotEmpty())<p class="portal-table-hint"><i data-lucide="move-horizontal" aria-hidden="true"></i>{{ __('corporate.portal_table_hint') }}</p>@endif
<div class="portal-table-wrap portal-table-wrap--journeys" tabindex="0" role="region" aria-label="{{ __('corporate.trips') }}">
<table role="table"><thead role="rowgroup"><tr role="row"><th scope="col" role="columnheader">{{ __('corporate.trip') }}</th><th scope="col" role="columnheader">{{ __('corporate.portal_passenger') }}</th><th scope="col" role="columnheader">{{ __('corporate.route') }}</th><th scope="col" role="columnheader">{{ __('corporate.status') }}</th><th scope="col" role="columnheader">{{ __('corporate.amount') }}</th></tr></thead>
<tbody role="rowgroup">
@forelse($trips as $trip)
    <tr role="row">
        <td role="cell"><a href="{{ route('corporate.portal.trips.show', $trip->id) }}">#{{ $trip->id }}</a><small>{{ $trip->created_at?->timezone($account->billing_timezone)?->format('d M Y H:i') }} {{ $account->billing_timezone }}</small></td>
        <td role="cell" data-label="{{ __('corporate.portal_passenger') }}">{{ trim(($trip->users?->first_name ?? '').' '.($trip->users?->last_name ?? '')) }}</td>
        <td role="cell" class="portal-route"><span><i data-lucide="circle-dot" aria-hidden="true"></i>{{ $trip->pickup_location }}</span><span><i data-lucide="map-pin" aria-hidden="true"></i>{{ $trip->drop_location }}</span></td>
        <td role="cell"><span class="portal-badge {{ in_array($trip->getRawOriginal('status'), ['Completed','completed']) ? 'portal-badge--success' : '' }}">{{ __('corporate.states.'.$trip->getRawOriginal('status')) }}</span><small>{{ __('corporate.billing_states.'.$trip->billing_status) }}</small></td>
        <td role="cell" class="portal-money" data-label="{{ __('corporate.amount') }}">{{ $trip->corporate_currency_code ?? $account->currency_code }} {{ $trip->corporate_amount ?? __('corporate.billing_states.pending') }}</td>
    </tr>
@empty
    <tr role="row"><td role="cell" colspan="5"><div class="portal-empty"><span class="portal-icon"><i data-lucide="route" aria-hidden="true"></i></span><strong>{{ __('corporate.no_trips') }}</strong><p>{{ __('corporate.portal_trips_empty_hint') }}</p></div></td></tr>
@endforelse
</tbody></table>
</div>
