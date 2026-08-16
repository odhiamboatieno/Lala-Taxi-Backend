<?php

namespace Modules\UserLevels\Services;

use App\Models\Company;
use App\Models\Rating;
use App\Models\Trips;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Modules\UserLevels\Entities\UserLevel;
use Modules\UserLevels\Entities\UserLevelAssignment;

class UserLevelService
{
    protected $companyLevelResolver = null;

    public function __construct(
        protected LevelScopeResolver $scopeResolver,
        $companyLevelResolver = null,
    ) {
        $this->companyLevelResolver = $companyLevelResolver;
    }

    protected function resolveCompanyLevelResolver()
    {
        if ($this->companyLevelResolver === null) {
            $fqcn = 'Modules\\SaaSDispatch\\Services\\CompanyLevelResolver';
            if (class_exists($fqcn)) {
                $this->companyLevelResolver = app($fqcn);
            } else {
                $this->companyLevelResolver = new class {
                    public function isCompanyMode(): bool
                    {
                        return false;
                    }

                    public function effectiveLevels(string $type, int $companyId): Collection
                    {
                        return collect();
                    }
                };
            }
        }
        return $this->companyLevelResolver;
    }

    public function currentCompanyId(): ?int
    {
        return $this->scopeResolver->currentCompanyId();
    }

    public function scopedLevelsQuery(string $type, ?int $companyId = null): Builder
    {
        return UserLevel::query()
            ->where('level_type', $type)
            ->where('company_id', $companyId)
            ->orderBy('sequence');
    }

    /**
     * Levels the current user (admin or company) should see.  This is
     * the SaaS-aware "effective" view: it merges the admin defaults with
     * any per-company overrides via `Modules\SaaSDispatch\Services\CompanyLevelResolver`.
     */
    public function effectiveLevels(string $type, ?int $companyId = null): Collection
    {
        $resolver = $this->resolveCompanyLevelResolver();

        // In `admin` mode (or when the SaaS module is not installed) we
        // just return the legacy scoped collection.
        if (! $resolver->isCompanyMode()) {
            return $this->scopedLevelsQuery($type, $companyId)->get();
        }

        $companyId = $companyId ?: $this->currentCompanyId() ?: (function_exists('saas_platform_company_id') ? saas_platform_company_id() : 0);

        return $resolver->effectiveLevels($type, (int) $companyId);
    }

    public function selectableLevels(string $type, ?int $companyId = null): Collection
    {
        $effective = $this->effectiveLevels($type, $companyId);
        if ($effective->isNotEmpty() && $effective->first() instanceof UserLevel) {
            return $effective->mapWithKeys(fn (UserLevel $l) => [$l->id => $l->effectiveName() ?: $l->name]);
        }

        return $this->scopedLevelsQuery($type, $companyId)
            ->pluck('name', 'id');
    }


    public function defaultLevel(string $type, ?int $companyId = null): ?UserLevel
    {
        $effective = $this->effectiveLevels($type, $companyId);
        $default = $effective->firstWhere('is_default', true);
        if ($default) {
            return $default;
        }

        return $effective->first();
    }


    public function assignmentForUser(int $userId, string $type, ?int $companyId = null): ?UserLevelAssignment
    {
        return UserLevelAssignment::query()
            ->with('level')
            ->where('user_id', $userId)
            ->where('level_type', $type)
            ->where('company_id', $companyId)
            ->first();
    }

    public function assignmentOrDefault(User $user, string $type, ?int $companyId = null): ?UserLevelAssignment
    {
        $assignment = $this->assignmentForUser($user->id, $type, $companyId);

        if ($assignment && $assignment->level) {
            return $assignment;
        }

        return $this->assignLevel($user, $type, $companyId, null, false);
    }

    public function assignLevel(User $user, string $type, ?int $companyId = null, ?int $levelId = null, bool $recalculate = true): ?UserLevelAssignment
    {
        $level = $levelId
            ? $this->scopedLevelsQuery($type, $companyId)->whereKey($levelId)->first()
            : $this->defaultLevel($type, $companyId);

        if (! $level) {
            return null;
        }

        $metrics = $this->metricsForUser($user, $type, $companyId);

        $assignment = UserLevelAssignment::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'company_id' => $companyId,
                'level_type' => $type,
            ],
            [
                'user_level_id' => $level->id,
                'points' => (float) ($metrics['points'] ?? 0),
                'metrics_snapshot' => $metrics,
                'assigned_at' => now(),
            ]
        );

        if ($recalculate) {
            return $this->recalculateAssignment($user, $type, $companyId);
        }

        return $assignment->load('level');
    }

    public function recalculateAssignment(User $user, string $type, ?int $companyId = null): ?UserLevelAssignment
    {
        // Use the SaaS-aware "effective" view so a company that inherited
        // an admin level and adjusted its thresholds is promoted using
        // the merged values.
        $levels = $this->effectiveLevels($type, $companyId)
            ->filter(fn (UserLevel $l) => $l->effectiveStatus() === 'Active');

        if ($levels->isEmpty()) {
            return null;
        }

        $metrics = $this->metricsForUser($user, $type, $companyId);
        $selected = $this->defaultLevel($type, $companyId);

        foreach ($levels as $level) {
            if ($this->meetsTargets($metrics, $level->effectiveTargetConfig())) {
                $selected = $level;
            }
        }


        return UserLevelAssignment::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'company_id' => $companyId,
                'level_type' => $type,
            ],
            [
                'user_level_id' => $selected?->id,
                'points' => (float) ($metrics['points'] ?? 0),
                'metrics_snapshot' => $metrics,
                'assigned_at' => now(),
            ]
        )->load('level');
    }

    public function metricsForUser(User $user, string $type, ?int $companyId = null): array
    {
        return $type === 'driver'
            ? $this->driverMetrics($user, $companyId)
            : $this->riderMetrics($user, $companyId);
    }

    public function summaryForIndex(string $type, ?int $companyId = null): array
    {
        if ($type === 'driver') {
            $userQuery = User::query()->where('user_type', 'Driver');

            if ($companyId !== null) {
                $userQuery->where('company_id', $companyId);
            }

            $vehicleCounts = DB::table('users')
                ->leftJoin('vehicle', function ($join) {
                    $join->on('vehicle.user_id', '=', 'users.id')->where('vehicle.default_type', '=', '1');
                })
                ->leftJoin('car_type', 'car_type.id', '=', 'vehicle.vehicle_id')
                ->where('users.user_type', 'Driver')
                ->when($companyId !== null, fn ($query) => $query->where('users.company_id', $companyId))
                ->selectRaw('SUM(CASE WHEN car_type.car_name LIKE "%car%" THEN 1 ELSE 0 END) as car_drivers')
                ->selectRaw('SUM(CASE WHEN car_type.car_name LIKE "%bike%" OR car_type.car_name LIKE "%motor%" THEN 1 ELSE 0 END) as bike_drivers')
                ->first();

            return [
                'total' => (clone $userQuery)->count(),
                'active' => (clone $userQuery)->where('status', 'Active')->count(),
                'inactive' => (clone $userQuery)->where('status', 'Inactive')->count(),
                'car_drivers' => (int) ($vehicleCounts->car_drivers ?? 0),
                'bike_drivers' => (int) ($vehicleCounts->bike_drivers ?? 0),
            ];
        }

        $userQuery = User::query()->where('user_type', 'Rider');
        if ($companyId !== null && \Schema::hasColumn('users', 'company_id')) {
            $userQuery->where('company_id', $companyId);
        }

        $newCustomerIds = Trips::query()
            ->select('user_id')
            ->when($companyId !== null && \Schema::hasColumn('trips', 'company_id'), fn ($query) => $query->where('company_id', $companyId))
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) <= 1')
            ->pluck('user_id');

        return [
            'total' => (clone $userQuery)->count(),
            'active' => (clone $userQuery)->where('status', 'Active')->count(),
            'inactive' => (clone $userQuery)->where('status', 'Inactive')->count(),
            'new' => $newCustomerIds->count(),
        ];
    }

    public function levelCards(string $type, ?int $companyId = null): Collection
    {
        return $this->effectiveLevels($type, $companyId)
            ->map(function (UserLevel $level) use ($type) {
                return [
                    'id' => $level->id,
                    'name' => $level->effectiveName() ?: $level->name,
                    'icon' => $level->effectiveIcon() ?: $level->icon,
                    'status' => $level->effectiveStatus(),
                    'inherited' => $level->isInherited(),
                    'parent_id' => $level->inherits_from_id,
                    'count' => UserLevelAssignment::query()
                        ->where('level_type', $type)
                        ->where('user_level_id', $level->id)
                        ->count(),
                ];
            });
    }


    protected function driverMetrics(User $user, ?int $companyId = null): array
    {
        $tripQuery = Trips::query()->where('driver_id', $user->id);

        if ($companyId !== null && \Schema::hasColumn('trips', 'company_id')) {
            $tripQuery->where('company_id', $companyId);
        }

        $completedTrips = (clone $tripQuery)->where('status', 'Completed')->count();
        $totalTrips = (clone $tripQuery)->count();
        $cancelledTrips = (clone $tripQuery)->whereIn('status', ['Cancelled', 'cancelled'])->count();
        $earningsColumn = \Schema::hasColumn('trips', 'driver_payout')
            ? 'driver_payout'
            : (\Schema::hasColumn('trips', 'driver_earnings') ? 'driver_earnings' : null);
        $earnings = $earningsColumn ? (clone $tripQuery)->sum($earningsColumn) : 0;
        $rating = (float) getRiderRating($user->id);
        $successRate = $totalTrips > 0 ? round(($completedTrips / $totalTrips) * 100, 2) : 0;
        $cancellationRate = $totalTrips > 0 ? round(($cancelledTrips / $totalTrips) * 100, 2) : 0;

        $points = ($completedTrips * 10) + ($earnings / 100) + ($rating * 20) - ($cancellationRate * 2);

        return [
            'completed_trips' => $completedTrips,
            'total_trips' => $totalTrips,
            'cancelled_trips' => $cancelledTrips,
            'earnings' => round((float) $earnings, 2),
            'rating' => $rating,
            'success_rate' => $successRate,
            'cancellation_rate' => $cancellationRate,
            'points' => round($points, 2),
        ];
    }

    protected function riderMetrics(User $user, ?int $companyId = null): array
    {
        $tripQuery = Trips::query()->where('user_id', $user->id);

        if ($companyId !== null && \Schema::hasColumn('trips', 'company_id')) {
            $tripQuery->where('company_id', $companyId);
        }

        $completedTrips = (clone $tripQuery)->where('status', 'Completed')->count();
        $totalTrips = (clone $tripQuery)->count();
        $cancelledTrips = (clone $tripQuery)->whereIn('status', ['Cancelled', 'cancelled'])->count();
        $spend = (clone $tripQuery)->sum(DB::raw('COALESCE(total_fare, 0)'));
        $digitalPayments = (clone $tripQuery)->whereNotIn('payment_mode', ['Cash', 'Cash & Wallet'])->count();
        $reviewsGiven = Rating::query()->where('user_id', $user->id)->where('driver_rating', '>', 0)->count();
        $rating = (float) getDriverRating($user->id);
        $successRate = $totalTrips > 0 ? round(($completedTrips / $totalTrips) * 100, 2) : 0;
        $cancellationRate = $totalTrips > 0 ? round(($cancelledTrips / $totalTrips) * 100, 2) : 0;

        $points = ($completedTrips * 8) + ($spend / 200) + ($reviewsGiven * 5) + ($rating * 10) - ($cancellationRate * 2);

        return [
            'completed_trips' => $completedTrips,
            'total_trips' => $totalTrips,
            'cancelled_trips' => $cancelledTrips,
            'spend' => round((float) $spend, 2),
            'digital_payments' => $digitalPayments,
            'reviews_given' => $reviewsGiven,
            'rating' => $rating,
            'success_rate' => $successRate,
            'cancellation_rate' => $cancellationRate,
            'points' => round($points, 2),
        ];
    }

    protected function meetsTargets(array $metrics, array $targets): bool
    {
        $rules = [
            'minimum_completed_trips' => fn () => ($metrics['completed_trips'] ?? 0) >= (int) ($targets['minimum_completed_trips'] ?? 0),
            'minimum_earnings' => fn () => ($metrics['earnings'] ?? $metrics['spend'] ?? 0) >= (float) ($targets['minimum_earnings'] ?? 0),
            'minimum_spend' => fn () => ($metrics['spend'] ?? 0) >= (float) ($targets['minimum_spend'] ?? 0),
            'maximum_cancellation_rate' => fn () => ($metrics['cancellation_rate'] ?? 0) <= (float) ($targets['maximum_cancellation_rate'] ?? 100),
            'minimum_rating' => fn () => ($metrics['rating'] ?? 0) >= (float) ($targets['minimum_rating'] ?? 0),
            'minimum_reviews' => fn () => ($metrics['reviews_given'] ?? 0) >= (int) ($targets['minimum_reviews'] ?? 0),
        ];

        foreach ($targets as $key => $value) {
            if ($value === null || $value === '' || ! isset($rules[$key])) {
                continue;
            }

            if (! $rules[$key]()) {
                return false;
            }
        }

        return true;
    }

    public function profileCompletion(User $user, string $type): int
    {
        $fields = [
            filled($user->first_name),
            filled($user->last_name),
            filled($user->email),
            filled($user->mobile_number),
            filled($user->country_id),
            filled(optional($user->profile_picture)->src),
        ];

        if ($type === 'driver') {
            $fields[] = filled(optional($user->driver_address)->address_line1);
            $fields[] = $user->driver_documents()->count() > 0;
            $fields[] = $user->vehicles()->count() > 0;
        } else {
            $fields[] = filled(optional($user->rider_location)->home);
            $fields[] = filled(optional($user->rider_location)->work);
        }

        $total = count($fields);
        $completed = collect($fields)->filter()->count();

        return $total > 0 ? (int) round(($completed / $total) * 100) : 0;
    }

    public function companyOptions(): Collection
    {
        return Company::query()->orderBy('name')->pluck('name', 'id');
    }
}
