<?php

namespace Modules\SaaSDispatch\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Modules\UserLevels\Entities\UserLevel;

/**
 * Centralized SaaS helper for driver / customer levels.
 *
 * Re-uses the existing `Modules\UserLevels\Entities\UserLevel` model and
 * does **not** introduce new tables.  It exposes a tiny API that lets
 * the rest of the application (services, controllers, jobs, views)
 * obtain a tenant-scoped collection of effective levels that merge the
 * admin defaults with company-specific overrides.
 *
 * Behaviour:
 *
 *   - When `user_level_owner() === 'admin'` (default) every tenant uses
 *     the global levels (`company_id = NULL`); any company-owned rows
 *     are ignored.
 *
 *   - When `user_level_owner() === 'company'` companies may:
 *       a) inherit an admin level via `inherits_from_id` and override
 *          only the values that matter to them; or
 *       b) define their own levels from scratch.
 *
 *     The "effective" view merges the company override on top of the
 *     parent admin level.
 */
class CompanyLevelResolver
{
    public function isAdminMode(): bool
    {
        return ! function_exists('user_level_owner')
            || user_level_owner() === 'admin';
    }

    public function isCompanyMode(): bool
    {
        return function_exists('user_level_owner')
            && user_level_owner() === 'company';
    }

    /**
     * Levels the admin (platform owner) has configured.  These are the
     * candidates a company can adopt / override.
     */
    public function inheritableAdminLevels(string $type): Collection
    {
        if (! class_exists(UserLevel::class) || ! \Schema::hasTable('user_levels')) {
            return collect();
        }

        return UserLevel::query()
            ->where('level_type', $type)
            ->whereNull('company_id')
            ->orderBy('sequence')
            ->get();
    }

    /**
     * The list of levels a tenant currently sees, with the effective
     * configuration applied.
     *
     *   - `admin` mode: just the global rows.
     *   - `company` mode: the union of (global rows) + (company owned
     *     rows).  When the company has adopted a global row via
     *     `inherits_from_id` the global row is hidden to avoid showing
     *     duplicates – the company row replaces it.
     */
    public function effectiveLevels(string $type, int $companyId): Collection
    {
        if (! class_exists(UserLevel::class) || ! \Schema::hasTable('user_levels')) {
            return collect();
        }

        $adminLevels = UserLevel::query()
            ->where('level_type', $type)
            ->whereNull('company_id')
            ->orderBy('sequence')
            ->get();

        $companyLevels = UserLevel::query()
            ->where('level_type', $type)
            ->where('company_id', $companyId)
            ->orderBy('sequence')
            ->get();

        // In `admin` mode companies cannot configure their own levels;
        // return the global rows only.
        if ($this->isAdminMode()) {
            return $adminLevels;
        }

        // Hide the admin row whenever the company has adopted it so the
        // UI does not show the same level twice.
        $adoptedAdminIds = $companyLevels
            ->pluck('inherits_from_id')
            ->filter()
            ->all();

        $filtered = $adminLevels->reject(function (UserLevel $level) use ($adoptedAdminIds) {
            return in_array($level->id, $adoptedAdminIds, true);
        });

        return $filtered->values()->merge($companyLevels->values())->values();
    }

    /**
     * Adopt (inherit) an admin level for the given company.  A new
     * company-owned level is created with `inherits_from_id` pointing at
     * the admin level; only the fields the company explicitly sets are
     * stored on the new row, so the admin defaults flow through.
     */
    public function inherit(int $companyId, int $adminLevelId, array $overrides = []): UserLevel
    {
        $adminLevel = UserLevel::query()
            ->whereNull('company_id')
            ->whereKey($adminLevelId)
            ->firstOrFail();

        $existing = UserLevel::query()
            ->where('company_id', $companyId)
            ->where('inherits_from_id', $adminLevelId)
            ->first();

        $level = $existing ?: new UserLevel();

        $level->company_id = $companyId;
        $level->level_type = $adminLevel->level_type;
        $level->inherits_from_id = $adminLevel->id;
        $level->sequence = (int) ($overrides['sequence'] ?? $adminLevel->sequence);
        $level->name = $overrides['name'] ?? $adminLevel->name;
        $level->slug = \Illuminate\Support\Str::slug($level->name);
        $level->icon = $overrides['icon'] ?? $adminLevel->icon;
        $level->description = $overrides['description'] ?? $adminLevel->description;
        $level->status = $overrides['status'] ?? 'Active';
        $level->is_default = (bool) ($overrides['is_default'] ?? false);

        // The company may pass partial reward / target overrides.  When
        // a key is missing we keep the parent's config so the admin
        // defaults flow through.
        $level->reward_config = $this->mergePartialConfig(
            $adminLevel->reward_config ?? [],
            $overrides['reward_config'] ?? []
        );
        $level->target_config = $this->mergePartialConfig(
            $adminLevel->target_config ?? [],
            $overrides['target_config'] ?? []
        );

        $level->save();

        return $level;
    }

    /**
     * Drop the inheritance link but keep the company's own level row.
     * From this point on the company is responsible for the values.
     */
    public function unlink(int $companyId, int $levelId): UserLevel
    {
        $level = UserLevel::query()
            ->where('company_id', $companyId)
            ->whereKey($levelId)
            ->whereNotNull('inherits_from_id')
            ->firstOrFail();

        $level->inherits_from_id = null;
        $level->save();

        return $level;
    }

    /**
     * Selectable levels for `<select>` form inputs.
     * The map uses the *effective* level id so any saved value still
     * resolves correctly even after the company creates its own level.
     */
    public function selectableLevels(string $type, int $companyId): Collection
    {
        return $this->effectiveLevels($type, $companyId)
            ->mapWithKeys(function (UserLevel $level) {
                return [$level->id => $level->effectiveName() ?: $level->name];
            });
    }

    /**
     * Lookup that a tenant can use to fetch a single level by id.  The
     * effective view is returned when the level belongs to the tenant,
     * the admin row itself when `inherits_from_id` is null, or null
     * when nothing matches.
     */
    public function findEffective(string $type, int $companyId, int $levelId): ?UserLevel
    {
        return $this->effectiveLevels($type, $companyId)
            ->firstWhere('id', $levelId);
    }

    /**
     * Merge two config arrays.  Only non-empty values in the override
     * replace the corresponding key in the base.
     */
    protected function mergePartialConfig(array $base, array $overrides): array
    {
        foreach ($overrides as $key => $value) {
            if ($value === null || $value === '' || $value === 0 || $value === '0') {
                continue;
            }
            $base[$key] = $value;
        }

        return $base;
    }
}
