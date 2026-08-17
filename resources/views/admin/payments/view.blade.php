@extends('admin.template')

@section('main')
@php
    $rider = $result->users;
    $driver = $result->driver;
    $company = optional($driver)->company;
    $currencySymbol = optional($result->currency)->symbol ?: optional($result->currency)->original_symbol ?: ($result->currency_code ?? '$');
    $pickupLat = $result->pickup_latitude ?? null;
    $pickupLng = $result->pickup_longitude ?? null;
    $dropLat = $result->drop_latitude ?? null;
    $dropLng = $result->drop_longitude ?? null;
    $tripStatus = strtolower((string) ($result->status ?? 'pending'));
    $statusClass = $tripStatus === 'completed' ? 'is-success' : ($tripStatus === 'cancelled' ? 'is-danger' : 'is-warning');
    $driverName = trim((optional($driver)->first_name ?? '').' '.(optional($driver)->last_name ?? '')) ?: ($result->driver_name ?? 'Unassigned');
    $riderName = trim((optional($rider)->first_name ?? '').' '.(optional($rider)->last_name ?? '')) ?: 'Unknown Rider';
@endphp
<div class="content-wrapper payment-review-page">
    <section class="content-header payment-review-header">
        <div>
            <h1>Payment Review</h1>
            <p>Trip payment, receipt actions, route preview, and payout visibility.</p>
        </div>
        <ol class="breadcrumb">
            <li><a href="{{ url(LOGIN_USER_TYPE.'/dashboard') }}"><i class="fa fa-dashboard"></i> {{ __('messages.admin.home') }}</a></li>
            <li><a href="{{ url(LOGIN_USER_TYPE.'/payments') }}">Payments</a></li>
            <li class="active">Details</li>
        </ol>
    </section>

    <section class="content">
        <div class="payment-review-shell">
            <div class="payment-review-hero">
                <div>
                    <div class="payment-review-kicker">Receipt #{{ $result->id }}</div>
                    <h2>{{ $currencySymbol }}{{ number_format((float) ($result->total_fare ?? 0), 2) }}</h2>
                    <p>{{ $riderName }} with {{ $driverName }}</p>
                </div>
                <div class="payment-review-actions">
                    <a href="{{ $printReceiptUrl }}" target="_blank" class="btn btn-light">Print Receipt</a>
                    <a href="{{ $downloadReceiptUrl }}" class="btn btn-light">Download PDF</a>
                    <form method="POST" action="{{ $sendReceiptUrl }}" class="d-inline">
                        {{ csrf_field() }}
                        <button type="submit" class="btn btn-primary">Send to Rider</button>
                    </form>
                </div>
            </div>

            <div class="payment-review-grid">
                <div class="payment-review-main">
                    <div class="payment-card payment-card--route">
                        <div class="payment-card__head">
                            <h3>Route Preview</h3>
                            <span class="payment-badge {{ $statusClass }}">{{ ucfirst($result->status ?? 'Pending') }}</span>
                        </div>
                        <div class="payment-route-map" id="payment-route-map">
                            <div class="payment-route-fallback">
                                <svg viewBox="0 0 560 240" preserveAspectRatio="none" aria-hidden="true">
                                    <defs>
                                        <linearGradient id="routeStroke" x1="0%" x2="100%" y1="0%" y2="0%">
                                            <stop offset="0%" stop-color="var(--color-success)"></stop>
                                            <stop offset="100%" stop-color="var(--color-info)"></stop>
                                        </linearGradient>
                                    </defs>
                                    <rect width="560" height="240" rx="24" fill="var(--color-as-f8fafc)"></rect>
                                    <path d="M72 164 C148 52, 218 204, 302 120 S436 42, 494 106" fill="none" stroke="url(#routeStroke)" stroke-width="8" stroke-linecap="round"></path>
                                    <circle cx="72" cy="164" r="14" fill="var(--color-success)"></circle>
                                    <circle cx="494" cy="106" r="14" fill="var(--color-as-ef4444)"></circle>
                                </svg>
                            </div>
                        </div>
                        <div class="payment-route-points">
                            <div class="payment-point payment-point--pickup">
                                <span class="payment-point__dot"></span>
                                <div>
                                    <small>Pickup</small>
                                    <strong>{{ $result->pickup_location ?: 'N/A' }}</strong>
                                </div>
                            </div>
                            <div class="payment-point payment-point--drop">
                                <span class="payment-point__dot"></span>
                                <div>
                                    <small>Drop-off</small>
                                    <strong>{{ $result->drop_location ?: 'N/A' }}</strong>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="payment-info-grid">
                        <div class="payment-card">
                            <div class="payment-card__head">
                                <h3>Trip Snapshot</h3>
                            </div>
                            <div class="payment-definition-grid">
                                <div><span>Vehicle</span><strong>{{ optional($result->car_type)->car_name ?: 'N/A' }}</strong></div>
                                <div><span>Driver</span><strong>{{ $driverName }}</strong></div>
                                <div><span>Rider</span><strong>{{ $riderName }}</strong></div>
                                <div><span>Payment Mode</span><strong>{{ $result->payment_mode ?: 'N/A' }}</strong></div>
                                <div><span>Start</span><strong>{{ ($result->begin_trip && $result->begin_trip !== '0000-00-00 00:00:00') ? date('d M Y, H:i', strtotime($result->begin_trip)) : 'N/A' }}</strong></div>
                                <div><span>End</span><strong>{{ ($result->end_trip && $result->end_trip !== '0000-00-00 00:00:00') ? date('d M Y, H:i', strtotime($result->end_trip)) : 'N/A' }}</strong></div>
                                <div><span>Transaction ID</span><strong>{{ $result->transaction_id ?: ($result->paykey ?: 'N/A') }}</strong></div>
                                <div><span>Currency</span><strong>{{ $result->currency_code ?: 'N/A' }}</strong></div>
                            </div>
                        </div>

                        <div class="payment-card">
                            <div class="payment-card__head">
                                <h3>Receipt Breakdown</h3>
                            </div>
                            <div class="payment-breakdown-list">
                                @forelse($invoice_data as $invoice)
                                    <div class="payment-breakdown-row">
                                        <span>{{ $invoice['key'] }}</span>
                                        <strong>{{ $invoice['value'] }}</strong>
                                    </div>
                                @empty
                                    <div class="payment-breakdown-row">
                                        <span>Total Fare</span>
                                        <strong>{{ $currencySymbol }}{{ number_format((float) ($result->total_fare ?? 0), 2) }}</strong>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <aside class="payment-review-side">
                    <div class="payment-card">
                        <div class="payment-card__head">
                            <h3>Fare Summary</h3>
                        </div>
                        <div class="payment-metric-grid">
                            <div><span>Base Fare</span><strong>{{ $currencySymbol }}{{ number_format((float) ($result->base_fare ?? 0), 2) }}</strong></div>
                            <div><span>Service Fee</span><strong>{{ $currencySymbol }}{{ number_format((float) ($result->access_fee ?? 0), 2) }}</strong></div>
                            <div><span>Tips</span><strong>{{ $currencySymbol }}{{ number_format((float) ($result->tips ?? 0), 2) }}</strong></div>
                            <div><span>Toll Fee</span><strong>{{ $currencySymbol }}{{ number_format((float) ($result->toll_fee ?? 0), 2) }}</strong></div>
                            <div><span>Driver Payout</span><strong>{{ $currencySymbol }}{{ number_format((float) ($result->driver_payout ?? 0), 2) }}</strong></div>
                            <div><span>Total</span><strong class="payment-total">{{ $currencySymbol }}{{ number_format((float) ($result->total_fare ?? 0), 2) }}</strong></div>
                        </div>
                    </div>

                    <div class="payment-card">
                        <div class="payment-card__head">
                            <h3>Payout Details</h3>
                        </div>
                        <div class="payment-definition-stack">
                            <div><span>Driver Payout Account</span><strong>{{ optional($driver)->payout_id ?: '-' }}</strong></div>
                            <div><span>Rider Receipt Email</span><strong>{{ optional($rider)->email ?: '-' }}</strong></div>
                            @if(LOGIN_USER_TYPE != 'company')
                                <div><span>Company</span><strong>{{ optional($company)->name ?: 'Platform' }}</strong></div>
                            @endif
                            @if($tripStatus === 'cancelled')
                                <div><span>Cancelled By</span><strong>{{ optional($result->cancel)->cancelled_by ?: 'N/A' }}</strong></div>
                                <div><span>Cancellation Reason</span><strong>{{ optional(optional($result->cancel)->cancel_reason)->reason ?: optional($result->cancel)->cancelled_reason ?: 'N/A' }}</strong></div>
                            @endif
                        </div>
                    </div>

                    <div class="payment-card payment-card--footer">
                        <a class="btn btn-default w-100" href="{{ url(LOGIN_USER_TYPE.'/payments') }}">Back to Payments</a>
                    </div>
                </aside>
            </div>
        </div>
    </section>
</div>

<style>
    .payment-review-page {
        background: var(--color-as-f4f7fb);
        min-height: 100vh;
    }

    .payment-review-shell {
        max-width: 1440px;
        margin: 0 auto;
        padding: 0 18px 24px;
    }

    .payment-review-header {
        display: flex;
        align-items: end;
        justify-content: space-between;
        gap: 1rem;
    }

    .payment-review-header h1 {
        margin: 0;
        font-size: 2rem;
        font-weight: 800;
        color: var(--color-as-18233d);
    }

    .payment-review-header p {
        margin: .35rem 0 0;
        color: var(--color-as-6c7898);
    }

    .payment-review-hero {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.6rem 1.8rem;
        border-radius: 0;
        background: var(--color-as-182848);
        color: var(--color-white);
        box-shadow: 0 22px 40px var(--color-as-rgba-41-55-117-0-18);
        margin: 18px 0 20px;
    }

    .payment-review-kicker {
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .12em;
        opacity: .8;
    }

    .payment-review-hero h2 {
        margin: .35rem 0;
        font-size: 2.2rem;
        font-weight: 800;
    }

    .payment-review-actions {
        display: flex;
        gap: .75rem;
        flex-wrap: wrap;
    }

    .payment-review-grid {
        display: grid;
        grid-template-columns: minmax(0, 1.5fr) 360px;
        gap: 20px;
    }

    .payment-info-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 20px;
        margin-top: 20px;
    }

    .payment-card {
        background: var(--color-white);
        border: 1px solid var(--color-as-e6ebf4);
        border-radius: 0;
        padding: 1.35rem;
        box-shadow: 0 12px 30px var(--color-as-rgba-19-35-78-0-06);
    }

    .payment-card__head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .payment-card__head h3 {
        margin: 0;
        font-size: 1.08rem;
        font-weight: 800;
        color: var(--color-as-1a2544);
    }

    .payment-badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: .35rem .8rem;
        border-radius: 0;
        font-size: .75rem;
        font-weight: 800;
    }

    .payment-badge.is-success { background: var(--color-as-e8fbef); color: var(--color-as-159947); }
    .payment-badge.is-warning { background: var(--color-as-fff7df); color: var(--color-as-b7791f); }
    .payment-badge.is-danger { background: var(--color-as-ffe7e7); color: var(--color-as-d53f3f); }

    .payment-route-map {
        position: relative;
        min-height: 260px;
        border-radius: 0;
        overflow: hidden;
        background: var(--color-as-f8fafc);
        border: 1px solid var(--color-as-e8eef7);
    }

    .payment-route-fallback,
    .payment-route-fallback svg,
    #payment-route-map-canvas {
        width: 100%;
        height: 260px;
        display: block;
    }

    .payment-route-points {
        display: grid;
        gap: .8rem;
        margin-top: 1rem;
    }

    .payment-point {
        display: flex;
        align-items: flex-start;
        gap: .75rem;
        padding: .95rem 1rem;
        border-radius: 0;
        background: var(--color-as-f8fafc);
    }

    .payment-point__dot {
        width: 12px;
        height: 12px;
        margin-top: .3rem;
        border-radius: 0;
        flex: 0 0 auto;
    }

    .payment-point--pickup .payment-point__dot { background: var(--color-success); }
    .payment-point--drop .payment-point__dot { background: var(--color-as-ef4444); }

    .payment-point small,
    .payment-definition-grid span,
    .payment-definition-stack span,
    .payment-metric-grid span,
    .payment-breakdown-row span {
        display: block;
        color: var(--color-as-7b88a8);
        font-size: .76rem;
        margin-bottom: .2rem;
    }

    .payment-point strong,
    .payment-definition-grid strong,
    .payment-definition-stack strong,
    .payment-metric-grid strong,
    .payment-breakdown-row strong {
        color: var(--color-as-1c2642);
        font-size: .95rem;
        font-weight: 700;
    }

    .payment-definition-grid,
    .payment-metric-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 1rem;
    }

    .payment-definition-stack {
        display: grid;
        gap: 1rem;
    }

    .payment-breakdown-list {
        display: grid;
        gap: .8rem;
    }

    .payment-breakdown-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: .8rem;
        border-bottom: 1px solid var(--color-as-edf1f7);
    }

    .payment-breakdown-row:last-child {
        border-bottom: 0;
        padding-bottom: 0;
    }

    .payment-total {
        color: var(--color-as-4f46e5);
        font-size: 1.1rem;
    }

    .payment-card--footer {
        padding: 1rem;
    }

    .payment-review-actions .btn,
    .payment-card--footer .btn {
        border-radius: 0;
        min-height: 44px;
        font-weight: 700;
    }

    @media (max-width: 1199px) {
        .payment-review-grid {
            grid-template-columns: 1fr;
        }
    }

    @media (max-width: 767px) {
        .payment-review-hero,
        .payment-review-header {
            flex-direction: column;
            align-items: stretch;
        }

        .payment-info-grid,
        .payment-definition-grid,
        .payment-metric-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection

@push('scripts')
<script>
    (function () {
        var pickup = { lat: @json($pickupLat), lng: @json($pickupLng) };
        var drop = { lat: @json($dropLat), lng: @json($dropLng) };
        var mapKey = @json(site_settings('map_key'));

        function hasCoords(point) {
            return point && point.lat !== null && point.lng !== null && point.lat !== '' && point.lng !== '';
        }

        function drawMap() {
            if (!window.google || !google.maps || !hasCoords(pickup) || !hasCoords(drop)) {
                return;
            }

            var holder = document.getElementById('payment-route-map');
            if (!holder) {
                return;
            }

            holder.innerHTML = '<div id="payment-route-map-canvas"></div>';
            var map = new google.maps.Map(document.getElementById('payment-route-map-canvas'), {
                zoom: 12,
                center: pickup,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: false
            });

            new google.maps.Marker({ position: pickup, map: map, title: 'Pickup' });
            new google.maps.Marker({ position: drop, map: map, title: 'Drop-off' });

            var routeLine = new google.maps.Polyline({
                path: [pickup, drop],
                geodesic: true,
                strokeColor: LalaColors.info,
                strokeOpacity: 1,
                strokeWeight: 4,
            });
            routeLine.setMap(map);

            var bounds = new google.maps.LatLngBounds();
            bounds.extend(pickup);
            bounds.extend(drop);
            map.fitBounds(bounds);
        }

        if (mapKey && hasCoords(pickup) && hasCoords(drop)) {
            window.initAdminPaymentRouteMap = drawMap;
            var script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(mapKey) + '&callback=initAdminPaymentRouteMap';
            script.async = true;
            script.defer = true;
            document.head.appendChild(script);
        }
    })();
</script>
@endpush
