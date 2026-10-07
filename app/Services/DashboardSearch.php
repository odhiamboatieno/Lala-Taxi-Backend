<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class DashboardSearch
{
    /** Search only explicitly approved fields; credentials and private payloads never enter the index. */
    public function search(string $term, string $scope, ?int $companyId, callable $can, callable $featureEnabled): array
    {
        $results = [];
        $hasMore = false;
        $schemaColumns = [];
        $features = [];
        foreach ($this->sources($scope) as $source) {
            if (!$can($source['permission']) || (isset($source['feature']) && !($features[$source['feature']] ??= $featureEnabled($source['feature'])))) {
                continue;
            }
            $columns = $schemaColumns[$source['table']] ??= Schema::getColumnListing($source['table']);
            if (!in_array('id', $columns, true)) {
                continue; // Optional modules may not be installed or migrated.
            }
            $fields = array_values(array_intersect($source['fields'], $columns));
            $query = DB::table($source['table']);
            if ($scope === 'company' && !$this->scopeCompany($query, $source, $columns, $companyId)) {
                continue;
            }
            if (in_array('deleted_at', $columns, true)) {
                $query->whereNull('deleted_at');
            }
            if (isset($source['type'])) {
                $query->where('user_type', $source['type']);
            }
            $tokens = preg_split('/\s+/u', Str::lower(trim($term)), -1, PREG_SPLIT_NO_EMPTY);
            // "drivers Alice" and "invoice INV-001" work as well as plain names/IDs.
            $categoryWords = explode(' ', Str::lower($source['group'].' '.($source['aliases'] ?? '')));
            foreach ($tokens as $token) {
                if (in_array($token, $categoryWords, true)) {
                    continue;
                }
                $query->where(function (Builder $match) use ($token, $fields) {
                    // Escape wildcard characters, keeping searches literal and parameterized.
                    $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $token).'%';
                    foreach ($fields as $field) {
                        $match->orWhereRaw('LOWER('.DB::connection()->getQueryGrammar()->wrap($field).") LIKE ? ESCAPE '!'", [$pattern]);
                    }
                    $id = ltrim($token, '#');
                    if (ctype_digit($id)) {
                        $match->orWhere('id', $id);
                    } elseif (!$fields) {
                        $match->whereRaw('1 = 0');
                    }
                });
            }
            $rows = $query->orderByDesc('id')->limit(6)->get(array_unique(array_merge(['id'], $fields, array_intersect(['status', 'company_id'], $columns))));
            $hasMore = $hasMore || $rows->count() > 5;
            foreach ($rows->take(5) as $row) {
                $title = trim(implode(' ', array_filter(array_map(fn ($field) => $row->{$field} ?? '', $source['title']))));
                $detail = array_filter(array_map(fn ($field) => $row->{$field} ?? '', $source['detail']));
                $path = $source['path'];
                if (isset($source['edit']) && $can($source['editPermission'] ?? $source['permission'])) {
                    $path = str_replace('{id}', (string) $row->id, $source['edit']);
                } elseif ($source['table'] === 'users') {
                    $path .= '?'.http_build_query(['search' => ($row->email ?? '') ?: ($row->first_name ?? '')]);
                }
                if (isset($source['anchor'])) {
                    $path .= '#search-'.$source['table'].'-'.$row->id;
                }
                $results[] = [
                    'id' => $source['table'].':'.($source['type'] ?? '').':'.$row->id,
                    'title' => $title ?: $source['group'].' #'.$row->id,
                    'description' => Str::limit('#'.$row->id.' · '.implode(' · ', $detail), 180),
                    'group' => $source['group'],
                    'icon' => $source['icon'],
                    'url' => url($scope.'/'.$path),
                    'kind' => 'record',
                ];
            }
        }

        return ['results' => $results, 'hasMore' => $hasMore];
    }

    private function scopeCompany(Builder $query, array $source, array $columns, ?int $companyId): bool
    {
        if (!$companyId) {
            return false;
        }
        if ($source['table'] === 'trips') {
            if (!Schema::hasTable('users')) {
                return false;
            }
            $query->whereIn('driver_id', DB::table('users')->select('id')->where('company_id', $companyId)->where('user_type', 'Driver'));
        } elseif (in_array('company_id', $columns, true)) {
            $query->where('company_id', $companyId);
        } else {
            return false; // Fail closed if a tenant column is missing.
        }

        return true;
    }

    private function sources(string $scope): array
    {
        $entry = static fn ($table, $group, $permission, $path, $fields, $title, $detail, $icon) => compact('table', 'group', 'permission', 'path', 'fields', 'title', 'detail', 'icon');
        $people = ['first_name', 'last_name', 'email', 'mobile_number'];
        $sources = [
            $entry('users', 'Drivers', 'view_driver', 'driver', $people, ['first_name', 'last_name'], ['email', 'mobile_number', 'status'], 'fa-id-card') + ['type' => 'Driver', 'aliases' => 'driver', 'edit' => 'edit_driver/{id}', 'editPermission' => 'update_driver'],
            $entry('users', 'Customers', 'view_rider', 'rider', $people, ['first_name', 'last_name'], ['email', 'mobile_number', 'status'], 'bi-people') + ['type' => 'Rider', 'aliases' => 'customer rider riders', 'edit' => 'edit_rider/{id}', 'editPermission' => 'update_rider'],
            $entry('vehicle', 'Vehicles', 'manage_vehicle', 'vehicle', ['vehicle_number', 'vehicle_name', 'vehicle_id'], ['vehicle_number'], ['vehicle_name', 'status'], 'bi-car-front') + ['aliases' => 'vehicle fleet plate registration', 'edit' => 'edit_vehicle/{id}'],
            $entry('trips', 'Trips', 'manage_trips', 'trips', ['pickup_location', 'drop_location', 'status'], ['pickup_location'], ['drop_location', 'status'], 'bi-signpost-split') + ['aliases' => 'trip ride rides', 'edit' => 'view_trips/{id}'],
            $entry('schedule_ride', 'Bookings', 'manage_manual_booking', 'later_booking', ['pickup_location', 'drop_location', 'booking_contact_name', 'booking_contact_phone', 'status'], ['booking_contact_name', 'pickup_location'], ['drop_location', 'status'], 'bi-calendar-event') + ['aliases' => 'booking scheduled dispatch', 'edit' => 'manual_booking/{id}', 'feature' => 'manual_booking'],
        ];
        if ($scope === 'company') {
            $sources = array_merge($sources, [
                $entry('operation_team_members', 'Staff members', 'manage_operation_team_members', 'operation-team/members', $people, ['first_name', 'last_name'], ['email', 'mobile_number'], 'bi-people') + ['edit' => 'operation-team/members/{id}/edit'],
                $entry('operation_team_roles', 'Staff roles', 'manage_operation_team_roles', 'operation-team/roles', ['name', 'description'], ['name'], ['description'], 'bi-shield-check') + ['edit' => 'operation-team/roles/{id}/edit'],
                $entry('company_service_areas', 'Service areas', 'manage_company_service_areas', 'saas-dispatch/service-areas', ['name', 'city', 'zone_name', 'country'], ['name'], ['city', 'zone_name', 'status'], 'bi-geo-alt') + ['edit' => 'saas-dispatch/service-areas/{id}/edit', 'feature' => 'saas'],
                $entry('company_vehicle_types', 'Vehicle types', 'manage_company_vehicle_types', 'saas-dispatch/vehicle-types', ['name', 'description'], ['name'], ['description', 'status'], 'bi-truck-flatbed') + ['edit' => 'saas-dispatch/vehicle-types/{id}/edit', 'feature' => 'saas'],
                $entry('company_fee_settings', 'Fees', 'manage_company_fees', 'saas-dispatch/fee-settings', ['fee_name', 'fee_type', 'applies_to'], ['fee_name'], ['fee_type', 'status'], 'bi-cash-coin') + ['feature' => 'saas'],
                $entry('company_fare_rules', 'Fare rules', 'manage_company_fares', 'saas-dispatch/fare-rules', ['status', 'currency_code', 'base_fare'], [], ['currency_code', 'base_fare', 'status'], 'bi-cash-stack') + ['edit' => 'saas-dispatch/fare-rules/{id}/edit', 'feature' => 'saas'],
                $entry('company_corporate_accounts', 'Corporate accounts', 'manage_company_accounts_invoicing', 'saas-dispatch/accounts', ['account_name', 'contact_name', 'contact_email', 'contact_phone'], ['account_name'], ['contact_email', 'status'], 'fa-building') + ['feature' => 'accounts_invoicing', 'anchor' => true],
                $entry('company_corporate_invoices', 'Invoices', 'manage_company_accounts_invoicing', 'saas-dispatch/accounts', ['invoice_number', 'status'], ['invoice_number'], ['status'], 'bi-receipt-cutoff') + ['aliases' => 'invoice billing', 'feature' => 'accounts_invoicing', 'anchor' => true],
                $entry('company_message_threads', 'Messages', 'manage_company_messages', 'saas-dispatch/messages', ['subject', 'status'], ['subject'], ['status'], 'fa-comment') + ['feature' => 'messages', 'anchor' => true],
                $entry('saas_support_tickets', 'Support tickets', 'manage_company_support_tickets', 'saas-dispatch/support-system-status', ['subject', 'status', 'priority'], ['subject'], ['priority', 'status'], 'bi-life-preserver') + ['feature' => 'support_system_status', 'anchor' => true],
            ]);
        } else {
            $sources = array_merge($sources, [
                $entry('companies', 'Companies', 'view_company', 'company', ['name', 'legal_name', 'email', 'mobile_number', 'registration_reference'], ['name'], ['email', 'status'], 'fa-building') + ['aliases' => 'company', 'edit' => 'edit_company/{id}', 'editPermission' => 'update_company'],
                $entry('admins', 'Administrators', 'manage_admin', 'admin_user', ['username', 'email'], ['username'], ['email'], 'bi-people'),
                $entry('roles', 'Roles', 'manage_admin', 'roles', ['name', 'display_name', 'description'], ['display_name', 'name'], ['description'], 'bi-shield-check'),
                $entry('promo_code', 'Promo codes', 'manage_promo_code', 'promo_code', ['code', 'promo_code', 'description'], ['code', 'promo_code'], ['description', 'status'], 'bi-receipt-cutoff'),
                $entry('pages', 'Pages', 'manage_static_pages', 'pages', ['name', 'title', 'url'], ['name', 'title'], ['url'], 'bi-file-earmark'),
                $entry('saas_support_tickets', 'Support tickets', 'manage_saas_module', 'saas-dispatch/support-system-status', ['subject', 'status', 'priority'], ['subject'], ['priority', 'status'], 'bi-life-preserver'),
            ]);
        }

        return $sources;
    }
}
