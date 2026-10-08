<?php 

namespace App\Http\Middleware;

/**
 * This file is part of Entrust,
 * a role & permission management solution for Laravel.
 *
 * @license MIT
 * @package Zizaco\Entrust
 */

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Session;

class EntrustPermission
{
    const DELIMITER = '|';

    protected $auth;

    /**
     * Creates a new instance of the middleware.
     *
     * @param Guard $auth
     */
    public function __construct()
    {
        $this->auth = auth('admin');
        $this->company_auth = auth('company');

        // Roles can visible to company
        $this->company_role = ['manage_manual_booking','manage_trips','view_driver','create_driver','update_driver','delete_driver','view_rider','create_rider','update_rider','delete_rider','update_company','manage_statements','manage_cancel_trips','manage_owe_amount','manage_send_message','manage_driver_payments','manage_map','manage_heat_map','manage_payments','manage_rating','manage_requests','manage_vehicle','manage_company_driver_fees','manage_saas_module','manage_company_service_areas','manage_company_vehicle_types','manage_company_fares','manage_company_fees','manage_company_dispatch_settings','manage_company_branding','manage_company_domains','manage_driver_levels','manage_rider_levels','manage_operation_team_roles','manage_operation_team_members','manage_company_surge_pricing','manage_company_payment_gateways','manage_company_documents','manage_company_networking','manage_company_accounts_invoicing','manage_company_data_suite','manage_company_messages','manage_company_support_tickets'];
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  Closure $next
     * @param  $permissions
     * @return mixed
     */
    public function handle($request, Closure $next, $permissions)
    {
        if (!is_array($permissions)) {
            $permissions = explode(self::DELIMITER, $permissions);
        }

        //If URL is company And company user is guest than 404 
        if ($request->segment(1) === 'company' && ($this->company_auth->guest() || !$this->companyPermissionAllowed($permissions))) {
            abort(403);
        }
        else if ($request->segment(1) === 'company') {
            return $next($request);
        }

        if ($this->auth->guest() || !$this->auth->user()->hasPermission($permissions)) {
            abort(403);
        }

        return $next($request);
    }

    protected function companyPermissionAllowed(array $permissions): bool
    {
        if (!in_array($permissions[0], $this->company_role)) {
            return false;
        }

        $member = function_exists('current_operation_team_member') ? current_operation_team_member() : null;
        if (!$member && Session::has('operation_team_member_id')) {
            return false;
        }

        if (!$member) {
            return true;
        }

        if (!$member->status || !$member->role || !$member->role->status) {
            return false;
        }

        foreach ($permissions as $permission) {
            if ($member->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }
}
