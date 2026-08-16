@php
    $brandName = $site_name ?? 'LalaTaxi';
    $logo = $logo_url ?? url('images/logos/logo.png');
    $appLinks = $app_links ?? collect();
    $socialLinks = $join_us ?? collect();
    $footerPages = $company_pages ?? collect();
    $riderAppStore = optional($appLinks->get(0))->value ?? '';
    $driverAppStore = optional($appLinks->get(1))->value ?? '';
    $riderPlayStore = optional($appLinks->get(2))->value ?? '';
    $driverPlayStore = optional($appLinks->get(3))->value ?? '';
    $requestDemoUrl = url('signup_company');
    $companyPortalUrl = url('signin_company');
    $loginUrl = url('signin');
    $passengerPortalUrl = Auth::check() && optional(Auth::user())->user_type === 'Rider' ? url('profile') : url('signin_rider');
    $driverPortalUrl = Auth::check() && optional(Auth::user())->user_type === 'Driver' ? url('driver_profile') : url('signin_driver');
    $companySignupUrl = url('signup_company');
    $footerLanguageOptions = $language ?? collect(['en' => 'English']);
    $footerDefaultLanguage = $default_language[0] ?? (object) ['value' => 'en', 'name' => 'English'];
    $footerCurrencyOptions = $currency_select ?? collect(['USD']);
    $stats = [
        ['value' => '24/7', 'label' => 'Dispatch Ready', 'text' => 'Manual and automated booking flows stay available for round-the-clock operations.'],
        ['value' => 'Multi', 'label' => 'Company Support', 'text' => 'Operate one fleet or onboard multiple taxi operators under a shared platform.'],
        ['value' => '2 Apps', 'label' => 'Driver + Passenger', 'text' => 'Dedicated mobile experiences support booking, jobs, tracking, wallets and trip history.'],
        ['value' => 'SaaS', 'label' => 'Operator Portal', 'text' => 'Give each operator tools for company setup, fleet control, reporting and service growth.'],
    ];
    $modules = [
        ['icon' => 'fa-headset', 'title' => 'Dispatch Console', 'text' => 'Handle live bookings, dispatch queues, driver assignment and trip monitoring from one control center.'],
        ['icon' => 'fa-building', 'title' => 'Company Dashboard', 'text' => 'Manage staff access, settings, service areas, approvals and operational visibility for each operator.'],
        ['icon' => 'fa-id-card', 'title' => 'Driver Dashboard', 'text' => 'Track profiles, documents, status, trips, payouts and readiness across your active driver base.'],
        ['icon' => 'fa-user', 'title' => 'Passenger Dashboard', 'text' => 'Support bookings, ride history, saved details, wallets and account activity for end users.'],
        ['icon' => 'fa-taxi', 'title' => 'Fleet Management', 'text' => 'Organize vehicles, service classes, driver pairing and capacity for day-to-day operations.'],
        ['icon' => 'fa-briefcase', 'title' => 'Corporate Accounts', 'text' => 'Serve business clients with account-based booking workflows, trip oversight and invoicing support.'],
        ['icon' => 'fa-plane-departure', 'title' => 'Airport Transfers', 'text' => 'Configure dependable pickup workflows for airport runs, advance scheduling and time-sensitive trips.'],
        ['icon' => 'fa-calendar-check-o', 'title' => 'Scheduled Bookings', 'text' => 'Capture pre-booked demand and prepare drivers for later trips without losing real-time visibility.'],
        ['icon' => 'fa-credit-card', 'title' => 'Payments and Wallets', 'text' => 'Support rider payments, wallet balances and trip settlement workflows inside the platform.'],
        ['icon' => 'fa-line-chart', 'title' => 'Reports and Analytics', 'text' => 'Review operational performance, booking activity and business signals with management-level reporting.'],
        ['icon' => 'fa-sitemap', 'title' => 'Operator Network', 'text' => 'Coordinate multiple operators, branches or partner fleets inside one structured dispatch ecosystem.'],
        ['icon' => 'fa-cogs', 'title' => 'SaaS Management', 'text' => 'Position LalaTaxi as a platform business with onboarding flows for transport companies and private hire operators.'],
    ];
    $dispatchWorkflows = [
        'Create bookings manually for phone, office, hotel or account-based requests.',
        'Assign the right driver based on availability, trip type and operational priority.',
        'Monitor live status changes from booking creation to trip completion.',
        'Prepare scheduled jobs, airport runs and other time-sensitive reservations.',
        'Track active drivers and react quickly to cancellations or service changes.',
        'Review fare context, route details and operational notes before dispatching.',
    ];
    $passengerFeatures = [
        'Web and app-based booking',
        'Live trip tracking',
        'Fare estimate visibility',
        'Trip history and receipts',
        'Wallet and payment support',
        'Scheduled ride booking',
    ];
    $driverFeatures = [
        'Incoming job requests',
        'Trip and route management',
        'Earnings visibility',
        'Online and offline status',
        'Scheduled job preparation',
        'Driver profile and performance',
    ];
    $operatorFeatures = [
        'Operator dashboard with business controls',
        'Staff roles and user access management',
        'Fleet oversight and driver assignment readiness',
        'Company settings, documents and approvals',
        'Reporting for trips, activity and operational growth',
        'Multi-branch and multi-company scaling support',
    ];
    $benefits = [
        ['title' => 'Built for dispatch operations', 'text' => 'LalaTaxi is structured around real booking desks, operator teams and active trip management rather than simple brochure booking.'],
        ['title' => 'Designed for all platform users', 'text' => 'Passengers, drivers, dispatchers and transport companies each get workflows that fit their responsibilities.'],
        ['title' => 'SaaS-ready from the start', 'text' => 'The platform can serve a single operator or grow into a multi-company dispatch ecosystem with operator onboarding.'],
        ['title' => 'Advanced booking workflows', 'text' => 'Support manual bookings, passenger bookings, later jobs, airport pickups and account-driven operational requests.'],
        ['title' => 'Real-time trip visibility', 'text' => 'Live driver location, trip status and dispatch awareness help teams stay ahead of service issues.'],
        ['title' => 'Flexible for regional taxi markets', 'text' => 'Adapt LalaTaxi to local private hire, taxi, fleet and mobility business models without rebuilding the core system.'],
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $brandName }} | Hybrid Taxi Dispatch and Mobility Platform</title>
    <meta name="description" content="LalaTaxi is a hybrid taxi dispatch platform for operators, fleets, drivers and passengers with booking, dispatch, tracking, payments and SaaS onboarding workflows.">
    <link rel="shortcut icon" href="{{ $favicon ?? asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ url('css/font-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ url('css/landing-dispatch.css') }}?v={{ $version ?? filemtime(public_path('css/landing-dispatch.css')) }}">
  <!-- lalatokens -->
  <link rel="stylesheet" href="{{ url('css/theme.css?v='.filemtime(public_path('css/theme.css'))) }}">
  <script src="{{ url('js/colors.js?v='.filemtime(public_path('js/colors.js'))) }}"></script>
  <!-- /lalatokens -->
</head>
<body class="landing-dispatch-page">
    <header class="landing-header">
        <div class="landing-shell">
            <div class="landing-header-bar">
                <a href="{{ url('/') }}" class="landing-brand" aria-label="{{ $brandName }} home">
                    <img src="{{ $logo }}" alt="{{ $brandName }}">
                    <div>
                        <strong>{{ $brandName }}</strong>
                        <span>Hybrid Taxi Dispatch Platform</span>
                    </div>
                </a>

                <nav class="landing-nav" aria-label="Primary">
                    <a href="{{ url('/') }}">Home</a>
                    <a href="#modules">Features</a>
                    <a href="#dispatch">Dispatch</a>
                    <a href="#experience">Driver</a>
                    <a href="#experience">Passenger</a>
                    <a href="#operators">Company</a>
                    <a href="#cta">Demo</a>
                </nav>

                <div class="landing-actions">
                    @if(!Auth::check() && !Auth::guard('company')->user())
                        <a class="landing-btn landing-btn-ghost" href="{{ $loginUrl }}">Login</a>
                    @elseif(Auth::guard('company')->user())
                        <a class="landing-btn landing-btn-ghost" href="{{ $companyPortalUrl }}">Company Portal</a>
                    @elseif(optional(Auth::user())->user_type === 'Rider')
                        <a class="landing-btn landing-btn-ghost" href="{{ url('profile') }}">Dashboard</a>
                    @else
                        <a class="landing-btn landing-btn-ghost" href="{{ url('driver_profile') }}">Dashboard</a>
                    @endif
                    <a class="landing-btn landing-btn-primary" href="{{ $requestDemoUrl }}">Request Demo</a>
                </div>

                <details class="landing-mobile-nav">
                    <summary aria-label="Open navigation">
                        <span></span>
                        <span></span>
                        <span></span>
                    </summary>
                    <div class="landing-mobile-panel">
                        <a href="{{ url('/') }}">Home</a>
                        <a href="#modules">Features</a>
                        <a href="#dispatch">Dispatch</a>
                        <a href="#experience">Driver & Passenger</a>
                        <a href="#operators">Company</a>
                        <a href="#hybrid-saas">SaaS</a>
                        <a href="#cta">Demo</a>
                        @if(!Auth::check() && !Auth::guard('company')->user())
                            <a href="{{ $loginUrl }}">Login</a>
                        @endif
                        <a class="landing-btn landing-btn-primary" href="{{ $requestDemoUrl }}">Request Demo</a>
                    </div>
                </details>
            </div>
        </div>
    </header>

    <main>
        <section class="hero-section">
            <div class="landing-shell hero-grid">
                <div class="hero-copy">
                    <span class="eyebrow">LalaTaxi | Hybrid Taxi Dispatch and Mobility Platform</span>
                    <h1>Modern Taxi Dispatch Software for Operators, Drivers and Passengers</h1>
                    <p>
                        LalaTaxi helps taxi companies, private hire operators and fleet businesses manage bookings, dispatch
                        drivers, track trips, onboard operators, control fleets and grow with a hybrid SaaS dispatch system
                        built for real-world transport operations.
                    </p>
                    <div class="hero-cta-row">
                        <a class="landing-btn landing-btn-primary" href="{{ $requestDemoUrl }}">Request Demo</a>
                        <a class="landing-btn landing-btn-secondary" href="#modules">Explore Features</a>
                    </div>
                    <div class="hero-pill-row">
                        <span>Manual booking</span>
                        <span>Driver management</span>
                        <span>Corporate accounts</span>
                        <span>Real-time tracking</span>
                    </div>
                </div>

                <div class="hero-visual">
                    <div class="dispatch-stage">
                        <div class="stage-window">
                            <div class="stage-window-bar">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>
                            <div class="stage-overview">
                                <div class="stage-stat">
                                    <small>Active dispatch board</small>
                                    <strong>148 live jobs</strong>
                                    <span>Queues balanced across city, airport and scheduled trips.</span>
                                </div>
                                <div class="stage-grid">
                                    <article>
                                        <label>Dispatch</label>
                                        <strong>Manual + instant</strong>
                                        <p>Call center, web and passenger bookings in one stream.</p>
                                    </article>
                                    <article>
                                        <label>Fleet</label>
                                        <strong>72 drivers online</strong>
                                        <p>Track readiness, service areas and vehicle availability in real time.</p>
                                    </article>
                                    <article>
                                        <label>Accounts</label>
                                        <strong>Corporate priority</strong>
                                        <p>Handle airport runs, account jobs and scheduled business travel.</p>
                                    </article>
                                    <article>
                                        <label>Operators</label>
                                        <strong>Multi-company ready</strong>
                                        <p>Run one operator or onboard multiple fleets under the same platform.</p>
                                    </article>
                                </div>
                            </div>
                        </div>
                        <div class="floating-card floating-card-left">
                            <span><i class="fa fa-map-marker"></i> Driver allocation</span>
                            <strong>Nearest qualified vehicle matched in seconds</strong>
                        </div>
                        <div class="floating-card floating-card-right">
                            <span><i class="fa fa-calendar"></i> Scheduled jobs</span>
                            <strong>Airport and later bookings prepared before pickup time</strong>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="trust-section">
            <div class="landing-shell">
                <div class="section-intro center">
                    <span class="section-kicker">Platform strengths</span>
                    <h2>Built to support modern dispatch operations without compromise</h2>
                    <p>
                        LalaTaxi brings together dispatch control, driver and passenger dashboards, multi-operator readiness,
                        scheduled bookings, fleet visibility and account-based service delivery in one commercial platform.
                    </p>
                </div>
                <div class="trust-grid">
                    @foreach($stats as $stat)
                        <article class="trust-card">
                            <span>{{ $stat['value'] }}</span>
                            <h3>{{ $stat['label'] }}</h3>
                            <p>{{ $stat['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="modules-section" id="modules">
            <div class="landing-shell">
                <div class="section-intro">
                    <span class="section-kicker">Platform modules</span>
                    <h2>Operational modules for dispatch, growth and day-to-day mobility management</h2>
                    <p>
                        Every core workflow is presented as part of a connected dispatch stack, helping operators move from
                        booking intake to trip completion, reporting and fleet expansion without fragmented tooling.
                    </p>
                </div>
                <div class="module-grid">
                    @foreach($modules as $module)
                        <article class="module-card">
                            <div class="module-icon">
                                <i class="fa {{ $module['icon'] }}"></i>
                            </div>
                            <h3>{{ $module['title'] }}</h3>
                            <p>{{ $module['text'] }}</p>
                            <a href="#cta">Learn More</a>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="dispatch-section" id="dispatch">
            <div class="landing-shell dispatch-layout">
                <div class="section-intro">
                    <span class="section-kicker">Dispatch workflow</span>
                    <h2>Designed for manual dispatch teams and real operator control rooms</h2>
                    <p>
                        LalaTaxi supports the operator workflow behind every serious taxi business, from call handling and
                        manual trip creation to driver assignment, airport scheduling, cancellation control and live trip
                        supervision.
                    </p>
                    <ul class="feature-list">
                        @foreach($dispatchWorkflows as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="dispatch-board">
                    <div class="dispatch-board-main">
                        <div class="board-header">
                            <div>
                                <small>Dispatch console</small>
                                <strong>Operator queue</strong>
                            </div>
                            <span>Live</span>
                        </div>
                        <div class="board-table">
                            <article>
                                <strong>Airport transfer</strong>
                                <span>Assigned to Driver 14</span>
                                <small>Pickup in 22 min</small>
                            </article>
                            <article>
                                <strong>Corporate booking</strong>
                                <span>Awaiting driver confirmation</span>
                                <small>Account ride</small>
                            </article>
                            <article>
                                <strong>Immediate city trip</strong>
                                <span>Driver arriving</span>
                                <small>ETA 4 min</small>
                            </article>
                            <article>
                                <strong>Scheduled hotel pickup</strong>
                                <span>Prepared in dispatch queue</span>
                                <small>06:30 tomorrow</small>
                            </article>
                        </div>
                    </div>
                    <div class="dispatch-side-card">
                        <small>Operational visibility</small>
                        <strong>Driver location, fare details and route context stay visible while teams dispatch and manage exceptions.</strong>
                    </div>
                </div>
            </div>
        </section>

        <section class="experience-section" id="experience">
            <div class="landing-shell">
                <div class="section-intro center">
                    <span class="section-kicker">Driver and passenger experience</span>
                    <h2>Two purpose-built experiences connected to the same dispatch engine</h2>
                    <p>
                        LalaTaxi gives passengers a dependable booking journey while giving drivers the job flow, trip tools
                        and earnings visibility required for consistent service delivery.
                    </p>
                </div>
                <div class="experience-grid">
                    <article class="experience-card">
                        <div class="experience-card-head">
                            <span>Passenger experience</span>
                            <a href="{{ $passengerPortalUrl }}">View Passenger Portal</a>
                        </div>
                        <h3>Booking and trip visibility built for repeat riders and account customers</h3>
                        <ul class="feature-list compact">
                            @foreach($passengerFeatures as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        <div class="mini-ui-card">
                            <strong>Passenger flow</strong>
                            <p>Book now, schedule later, track driver arrival and manage payments from the same account.</p>
                        </div>
                    </article>
                    <article class="experience-card dark">
                        <div class="experience-card-head">
                            <span>Driver experience</span>
                            <a href="{{ $driverPortalUrl }}">View Driver Portal</a>
                        </div>
                        <h3>Job-ready tools for active drivers, scheduled work and earnings management</h3>
                        <ul class="feature-list compact">
                            @foreach($driverFeatures as $item)
                                <li>{{ $item }}</li>
                            @endforeach
                        </ul>
                        <div class="mini-ui-card dark">
                            <strong>Driver flow</strong>
                            <p>Accept jobs, manage active trips, prepare for scheduled rides and stay visible to dispatch.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="operators-section" id="operators">
            <div class="landing-shell operator-layout">
                <div class="section-intro">
                    <span class="section-kicker">Company and operator tools</span>
                    <h2>Built for Taxi Companies That Need More Than Basic Booking</h2>
                    <p>
                        LalaTaxi is positioned for operators that need proper business controls, staff coordination, driver
                        oversight, reporting, approvals and the ability to expand into larger multi-branch or multi-company
                        structures.
                    </p>
                    <ul class="feature-list">
                        @foreach($operatorFeatures as $item)
                            <li>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
                <div class="operator-panels">
                    <article>
                        <small>Role-based control</small>
                        <strong>Separate business management from live dispatch operations.</strong>
                    </article>
                    <article>
                        <small>Fleet oversight</small>
                        <strong>Coordinate drivers, vehicles, availability and service quality from a single operator view.</strong>
                    </article>
                    <article>
                        <small>Growth readiness</small>
                        <strong>Expand into more branches, more fleets or more operators without replacing the platform.</strong>
                    </article>
                </div>
            </div>
        </section>

        <section class="saas-section" id="hybrid-saas">
            <div class="landing-shell saas-layout">
                <div class="section-intro">
                    <span class="section-kicker">Hybrid SaaS model</span>
                    <h2>Run LalaTaxi as a single-company dispatch system or a multi-operator SaaS platform</h2>
                    <p>
                        The same product can support one transport company or become the foundation for onboarding multiple
                        taxi operators, each with their own company profile, drivers, settings, booking operations and
                        management workflows under the platform.
                    </p>
                </div>
                <div class="saas-columns">
                    <article>
                        <h3>Single operator mode</h3>
                        <p>Launch a focused dispatch business with operator controls, booking tools, fleet visibility and customer-facing portals.</p>
                    </article>
                    <article>
                        <h3>Multi-operator mode</h3>
                        <p>Onboard more companies, let each operator manage their own setup and build a broader taxi network with SaaS-style administration.</p>
                    </article>
                </div>
            </div>
        </section>

        <section class="benefits-section">
            <div class="landing-shell">
                <div class="section-intro center">
                    <span class="section-kicker">Why LalaTaxi</span>
                    <h2>A dispatch-focused platform positioned for commercial growth</h2>
                    <p>
                        LalaTaxi is presented as a serious transport operations platform with the flexibility to support local
                        taxi markets, private hire operators and future addon expansion.
                    </p>
                </div>
                <div class="benefit-grid">
                    @foreach($benefits as $benefit)
                        <article class="benefit-card">
                            <h3>{{ $benefit['title'] }}</h3>
                            <p>{{ $benefit['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="cta-section" id="cta">
            <div class="landing-shell cta-panel">
                <div>
                    <span class="section-kicker">Launch your platform</span>
                    <h2>Ready to Launch or Upgrade Your Taxi Dispatch Platform?</h2>
                    <p>
                        Position LalaTaxi for operators, fleets, drivers and passengers with a dispatch-first platform that
                        is ready for direct business use or SaaS expansion.
                    </p>
                </div>
                <div class="cta-actions">
                    <a class="landing-btn landing-btn-primary" href="{{ $requestDemoUrl }}">Request Demo</a>
                    <a class="landing-btn landing-btn-secondary" href="{{ url('help') }}">Contact Sales</a>
                    <a class="landing-btn landing-btn-ghost-light" href="{{ $passengerPortalUrl }}">View Passenger Portal</a>
                    <a class="landing-btn landing-btn-ghost-light" href="{{ $driverPortalUrl }}">View Driver Portal</a>
                </div>
            </div>
        </section>
    </main>

    <footer class="landing-footer" id="footer">
        <div class="landing-shell">
            <div class="footer-grid">
                <div class="footer-brand-block">
                    <a href="{{ url('/') }}" class="landing-brand footer-brand">
                        <img src="{{ $logo }}" alt="{{ $brandName }}">
                        <div>
                            <strong>{{ $brandName }}</strong>
                            <span>Dispatch, booking and mobility management for modern operators.</span>
                        </div>
                    </a>

                    <div class="footer-store-row">
                        @if($riderAppStore)
                            <a href="{{ $riderAppStore }}" target="_blank" rel="noopener">
                                <img src="{{ url('images/new/app.png') }}" alt="Download rider app on the App Store">
                            </a>
                        @endif
                        @if($riderPlayStore)
                            <a href="{{ $riderPlayStore }}" target="_blank" rel="noopener">
                                <img src="{{ url('images/new/google.png') }}" alt="Get rider app on Google Play">
                            </a>
                        @endif
                    </div>
                </div>

                <div>
                    <h3>Company</h3>
                    <a href="{{ url('/') }}">Home</a>
                    <a href="{{ $requestDemoUrl }}">Request Demo</a>
                    <a href="{{ $companyPortalUrl }}">Company Portal</a>
                    @foreach($footerPages as $page)
                        <a href="{{ url($page->url) }}">{{ $page->name }}</a>
                    @endforeach
                </div>

                <div>
                    <h3>Platform</h3>
                    <a href="#modules">Features</a>
                    <a href="#dispatch">Dispatch</a>
                    <a href="{{ $passengerPortalUrl }}">Passenger Portal</a>
                    <a href="{{ $driverPortalUrl }}">Driver Portal</a>
                    <a href="{{ $companySignupUrl }}">Operator Onboarding</a>
                </div>

                <div>
                    <h3>Access</h3>
                    <a href="{{ $loginUrl }}">Login</a>
                    <a href="{{ url('signin_rider') }}">Passenger Login</a>
                    <a href="{{ url('signin_driver') }}">Driver Login</a>
                    <a href="{{ $companyPortalUrl }}">Company Login</a>
                    <a href="{{ url('help') }}">Help Center</a>
                </div>
            </div>

            <div class="footer-lower">
                <div class="footer-socials">
                    @for($i = 0; $i < $socialLinks->count(); $i++)
                        @if($socialLinks[$i]->value)
                            <a href="{{ $socialLinks[$i]->value }}" target="_blank" rel="noopener" aria-label="{{ $socialLinks[$i]->name }}">
                                <span class="fa fa-{{ str_replace('_', '-', $socialLinks[$i]->name) }}"></span>
                            </a>
                        @endif
                    @endfor
                </div>

                <div class="footer-selectors">
                    <div class="footer-select">
                        {!! Form::select('language', $footerLanguageOptions, Session::get('language') ? Session::get('language') : $footerDefaultLanguage->value, ['class' => 'landing-select', 'id' => 'js-language-select']) !!}
                    </div>
                    <div class="footer-select">
                        <select id="js-currency-select" class="landing-select">
                            @foreach($footerCurrencyOptions as $code)
                                <option value="{{ $code }}" @if(session('currency') == $code) selected="selected" @endif>{{ $code }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="footer-meta">
                <p>Copyright &copy; {{ $copyright_year }} <a href="{{ $copyright_url }}">{{ $copyright_name }}</a>. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <script>
        (function () {
            var csrfToken = document.querySelector('meta[name="csrf-token"]');
            var sessionEndpoint = @json(url('set_session'));

            function postSession(payload) {
                if (!csrfToken) {
                    return;
                }

                fetch(sessionEndpoint, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken.getAttribute('content'),
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify(payload)
                }).then(function () {
                    window.location.reload();
                });
            }

            var languageSelect = document.getElementById('js-language-select');
            var currencySelect = document.getElementById('js-currency-select');

            if (languageSelect) {
                languageSelect.addEventListener('change', function (event) {
                    postSession({ language: event.target.value });
                });
            }

            if (currencySelect) {
                currencySelect.addEventListener('change', function (event) {
                    postSession({ currency: event.target.value });
                });
            }
        })();
    </script>
</body>
</html>
