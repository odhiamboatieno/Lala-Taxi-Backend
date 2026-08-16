<?php

namespace Modules\UserLevels\Entities;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Company;

class UserLevel extends Model
{
    protected $table = 'user_levels';

    protected $guarded = [];

    protected $casts = [
        'target_config' => 'array',
        'reward_config' => 'array',
        'is_default' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'company_id', 'id');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(UserLevelAssignment::class, 'user_level_id', 'id');
    }

    /**
     * When a company-owned level is "inherited" from an admin level this
     * relationship points to the admin level that supplies the defaults.
     */
    public function inheritsFrom(): BelongsTo
    {
        return $this->belongsTo(self::class, 'inherits_from_id', 'id');
    }

    /**
     * The parent level that supplies the default values for this level.
     * Returns the level itself when there is no parent (i.e. a fully
     * independent level that has no fallback chain).
     */
    public function baseLevel(): self
    {
        if ($this->inherits_from_id && $this->inheritsFrom) {
            return $this->inheritsFrom;
        }

        return $this;
    }

    public function isInherited(): bool
    {
        return (bool) $this->inherits_from_id;
    }

    /**
     * Effective level name – company override first, admin default second.
     */
    public function effectiveName(): string
    {
        if (filled($this->name) && (! $this->isInherited() || $this->name !== $this->baseLevel()->name)) {
            return (string) $this->name;
        }

        return (string) ($this->baseLevel()->name ?? '');
    }

    public function effectiveIcon(): ?string
    {
        if (filled($this->icon)) {
            return $this->icon;
        }

        return $this->baseLevel()->icon;
    }

    public function effectiveDescription(): ?string
    {
        if (filled($this->description)) {
            return $this->description;
        }

        return $this->baseLevel()->description;
    }

    /**
     * Reward config with the company overrides layered on top of the
     * admin defaults.  An empty/zero value in the company row does not
     * override the parent – only a non-empty value does.  This matches
     * the "use the admin set or configured levels ... BUT set their own
     * values" requirement.
     */
    public function effectiveRewardConfig(): array
    {
        $parent = $this->baseLevel()->reward_config ?? [];
        $child = $this->reward_config ?? [];

        $merged = is_array($parent) ? $parent : [];
        foreach ((array) $child as $key => $value) {
            if ($value === null || $value === '' || $value === 0 || $value === '0') {
                continue;
            }
            $merged[$key] = $value;
        }

        return $merged;
    }

    public function effectiveTargetConfig(): array
    {
        $parent = $this->baseLevel()->target_config ?? [];
        $child = $this->target_config ?? [];

        $merged = is_array($parent) ? $parent : [];
        foreach ((array) $child as $key => $value) {
            if ($value === null || $value === '' || $value === 0 || $value === '0') {
                continue;
            }
            $merged[$key] = $value;
        }

        return $merged;
    }

    /**
     * The sequence a level should render at, with the company override
     * taking priority over the admin default.
     */
    public function effectiveSequence(): int
    {
        if ($this->sequence) {
            return (int) $this->sequence;
        }

        return (int) ($this->baseLevel()->sequence ?? 0);
    }

    public function effectiveStatus(): string
    {
        // Companies can deactivate a level they inherited; otherwise the
        // parent's status is used.
        if ($this->status === 'Inactive') {
            return 'Inactive';
        }

        return (string) ($this->baseLevel()->status ?? $this->status ?? 'Active');
    }
}
