@include('common.head')
@include('common.header')
<style>
    /* === Ride Preloader === */
#ride-preloader {
    position: fixed;
    inset: 0;
    background: var(--color-as-0f172a); /* dark map feel */
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 9999;
}

.map-container {
    position: relative;
    width: 180px;
    height: 180px;
    border-radius: 50%;
    overflow: hidden;
    background: var(--color-as-020617);
}

/* Map grid */
.map-grid {
    position: absolute;
    inset: 0;
    background-image:
        linear-gradient(var(--color-as-rgba-255-255-255-0-05) 1px, transparent 1px),
        linear-gradient(90deg, var(--color-as-rgba-255-255-255-0-05) 1px, transparent 1px);
    background-size: 20px 20px;
}

/* Radar scan */
.radar {
    position: absolute;
    width: 100%;
    height: 100%;
    border-radius: 50%;
    background: conic-gradient(
        from 0deg,
        var(--color-as-rgba-34-197-94-0-4),
        var(--color-as-rgba-34-197-94-0) 60%
    );
    animation: radar-spin 1.5s linear infinite;
}

/* Center pulse */
.pulse {
    position: absolute;
    top: 50%;
    left: 50%;
    width: 12px;
    height: 12px;
    background: var(--color-as-22c55e);
    border-radius: 50%;
    transform: translate(-50%, -50%);
    box-shadow: 0 0 0 var(--color-as-rgba-34-197-94-0-7);
    animation: pulse 1.5s infinite;
}

.loading-text {
    margin-top: 20px;
    color: var(--color-as-e5e7eb);
    font-size: 14px;
    letter-spacing: 0.5px;
}

@keyframes radar-spin {
    to { transform: rotate(360deg); }
}

@keyframes pulse {
    0% { box-shadow: 0 0 0 0 var(--color-as-rgba-34-197-94-0-7); }
    70% { box-shadow: 0 0 0 20px var(--color-as-rgba-34-197-94-0); }
    100% { box-shadow: 0 0 0 0 var(--color-as-rgba-34-197-94-0); }
}

.hidden {
    display: none;
}

</style>
{{-- Ride Search Preloader --}}
<div id="ride-preloader">
    <div class="map-container">
        <div class="map-grid"></div>
        <div class="radar"></div>
        <div class="pulse"></div>
    </div>
    <p class="loading-text">{{ __('messages.blade_static.almost_ready_c911f2e6') }}</p>
</div>

<main id="main" class="hidden">
    @yield('main')
</main>

@include('common.footer')
@include('common.foot')
