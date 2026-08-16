@php
    $footerYear = $copyright_year ?? site_settings('copyright_year') ?: date('Y');
    $footerUrl = $copyright_url ?? site_settings('copyright_url') ?: url('/');
    $footerName = $copyright_name ?? site_settings('copyright_name') ?: ($site_name ?? config('app.name'));
@endphp

<!-- Begin Footer -->
<footer class="footer">
    <div class="container-fluid">
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 text-center text-sm-start w-100">
            <div>
                Copyright &copy; {{ $footerYear }}
                <a href="{{ $footerUrl }}" class="text-reset fw-semibold">{{ $footerName }}</a>.
            </div>
            <div class="text-sm-end">
                {{ __('messages.blade_static.all_rights_reserved_3639ec0f') }}
            </div>
        </div>
    </div>
</footer>
<!-- END Footer -->
