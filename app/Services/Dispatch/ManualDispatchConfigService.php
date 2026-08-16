<?php

namespace App\Services\Dispatch;

use App\Models\Company;

class ManualDispatchConfigService
{
    public function defaults(): array
    {
        return [
            'minimum_scheduled_lead_minutes'   => (int) (site_settings('minimum_scheduled_lead_minutes') ?: 15),
            'allow_asap_manual_booking'        => (bool) (site_settings('allow_asap_manual_booking') ?: 1),
            'allow_dispatch_time_override'     => (bool) (site_settings('allow_dispatch_time_override') ?: 1),
            'scheduled_dispatch_lead_minutes'  => (int) (site_settings('scheduled_dispatch_lead_minutes') ?: 5),
            'default_assignment_timeout_seconds' => (int) (site_settings('default_assignment_timeout_seconds') ?: 30),
            'asap_assignment_timeout_seconds'  => (int) (site_settings('asap_assignment_timeout_seconds') ?: 20),
            'scheduled_assignment_timeout_seconds' => (int) (site_settings('scheduled_assignment_timeout_seconds') ?: 45),
            'max_assignment_attempts'          => (int) (site_settings('max_assignment_attempts') ?: 10),
            'enable_broadcast_dispatch'        => (bool) (site_settings('enable_broadcast_dispatch') ?: 1),
            'enable_force_dispatch'            => (bool) (site_settings('enable_force_dispatch') ?: 1),
            'enable_sequential_dispatch'       => (bool) (site_settings('enable_sequential_dispatch') ?: 1),
            'auto_expand_radius'               => (bool) (site_settings('auto_expand_radius') ?: 1),
            'default_radius_miles'             => (int) (site_settings('default_radius_miles') ?: 10),
        ];
    }

    public function resolve(?Company $company = null): array
    {
        $settings = $this->defaults();

        $companyOverrides = data_get($company, 'dispatch_settings', []);
        if (!is_array($companyOverrides)) {
            $companyOverrides = [];
        }

        foreach ($settings as $key => $value) {
            if (array_key_exists($key, $companyOverrides) && $companyOverrides[$key] !== null && $companyOverrides[$key] !== '') {
                $settings[$key] = is_bool($value)
                    ? filter_var($companyOverrides[$key], FILTER_VALIDATE_BOOLEAN)
                    : (is_int($value) ? (int) $companyOverrides[$key] : $companyOverrides[$key]);
            }
        }

        return $settings;
    }

    public function normalizeBookingMode(?string $mode): string
    {
        return strtolower((string) $mode) === 'scheduled' ? 'scheduled' : 'asap';
    }

    public function normalizeDispatchMode(?string $mode, bool $autoAssignEnabled = false): string
    {
        $mode = strtolower((string) $mode);
        $allowed = ['manual', 'auto', 'broadcast', 'sequential', 'force'];

        if (!in_array($mode, $allowed, true)) {
            return $autoAssignEnabled ? 'auto' : 'manual';
        }

        return $mode;
    }

    public function minimumLeadMinutes(string $bookingMode, array $settings, bool $override = false): int
    {
        if ($bookingMode === 'asap' || $override) {
            return 0;
        }

        return max(0, (int) ($settings['minimum_scheduled_lead_minutes'] ?? 15));
    }

    public function scheduledDispatchLeadMinutes(array $settings): int
    {
        return max(0, (int) ($settings['scheduled_dispatch_lead_minutes'] ?? 5));
    }

    public function assignmentTimeoutSeconds(string $bookingMode, array $settings): int
    {
        if ($bookingMode === 'asap') {
            return max(5, (int) ($settings['asap_assignment_timeout_seconds'] ?? $settings['default_assignment_timeout_seconds'] ?? 20));
        }

        return max(5, (int) ($settings['scheduled_assignment_timeout_seconds'] ?? $settings['default_assignment_timeout_seconds'] ?? 45));
    }
}
