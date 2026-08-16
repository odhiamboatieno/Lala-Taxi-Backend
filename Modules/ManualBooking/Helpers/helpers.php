<?php

if (!function_exists('manual_booking_module_enabled')) {
    function manual_booking_module_enabled(): bool
    {
        return site_settings(config('manual_booking.site_setting_key', 'manual_booking_module_enabled')) !== '0';
    }
}
