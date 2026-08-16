<?php

namespace Modules\UserLevels\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Modules\SaaSDispatch\Services\CompanyLevelResolver;
use Modules\UserLevels\Entities\UserLevel;
use Modules\UserLevels\Http\Requests\StoreUserLevelRequest;
use Modules\UserLevels\Services\UserLevelService;

abstract class BaseUserLevelController extends Controller
{
    public function __construct(
        protected UserLevelService $userLevelService,
        protected CompanyLevelResolver $companyLevelResolver
    ) {
    }

    abstract protected function levelType(): string;

    abstract protected function viewBase(): string;

    abstract protected function routeBase(): string;

    protected function companyId(): ?int
    {
        return $this->userLevelService->currentCompanyId();
    }

    /**
     * Whether the current request is in SaaS company scope.  When
     * `user_level_owner = 'admin'` the controller still allows the
     * platform owner to view company-owned rows (so admins can audit
     * tenant customizations) but companies can only edit their own.
     */
    protected function isCompanyScope(): bool
    {
        return $this->userLevelService->currentCompanyId() !== null;
    }

    public function index(Request $request)
    {
        $companyId = $this->companyId();
        $type = $this->levelType();
        $effective = $this->userLevelService->effectiveLevels($type, $companyId);

        // Search / status filters are still applied at the effective
        // collection level so the company can filter what it sees.
        if ($request->filled('status')) {
            $effective = $effective->filter(fn (UserLevel $l) => $l->effectiveStatus() === $request->status);
        }
        if ($request->filled('search')) {
            $needle = strtolower($request->search);
            $effective = $effective->filter(fn (UserLevel $l) => str_contains(strtolower($l->effectiveName()), $needle));
        }

        $perPage = 15;
        $page = max(1, (int) $request->get('page', 1));
        $paginated = new \Illuminate\Pagination\LengthAwarePaginator(
            $effective->values()->forPage($page, $perPage),
            $effective->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // The inheritable admin levels are passed to the view so the
        // company can adopt an admin level with one click.
        $inheritableAdminLevels = $this->companyLevelResolver->inheritableAdminLevels($type);

        return view($this->viewBase().'.index', [
            'type' => $type,
            'companyId' => $companyId,
            'title' => config('user_levels.types.'.$type.'.label'),
            'cards' => $this->userLevelService->levelCards($type, $companyId),
            'levels' => $paginated,
            'inheritableAdminLevels' => $inheritableAdminLevels,
            'companyLevelResolver' => $this->companyLevelResolver,
        ]);
    }

    public function create()
    {
        $type = $this->levelType();
        $inheritableAdminLevels = $this->companyLevelResolver->inheritableAdminLevels($type);

        return view($this->viewBase().'.form', [
            'type' => $type,
            'title' => 'Add '.config('user_levels.types.'.$this->levelType().'.singular'),
            'result' => new UserLevel([
                'status' => 'Active',
                'sequence' => $this->userLevelService->scopedLevelsQuery($this->levelType(), $this->companyId())->count() + 1,
            ]),
            'inheritableAdminLevels' => $inheritableAdminLevels,
            'companyLevelResolver' => $this->companyLevelResolver,
        ]);
    }

    public function store(StoreUserLevelRequest $request): RedirectResponse
    {
        $this->guardAdminOnlyMode();

        $level = new UserLevel();
        $this->fillLevel($level, $request);
        $level->save();

        flashMessage('success', config('user_levels.types.'.$this->levelType().'.singular').' created successfully.');

        return redirect()->route($this->routeBase().'.index');
    }

    public function edit(UserLevel $userLevel)
    {
        $this->ensureScope($userLevel);
        $inheritableAdminLevels = $this->companyLevelResolver->inheritableAdminLevels($this->levelType());

        return view($this->viewBase().'.form', [
            'type' => $this->levelType(),
            'title' => 'Edit '.config('user_levels.types.'.$this->levelType().'.singular'),
            'result' => $userLevel,
            'inheritableAdminLevels' => $inheritableAdminLevels,
            'companyLevelResolver' => $this->companyLevelResolver,
        ]);
    }

    public function update(StoreUserLevelRequest $request, UserLevel $userLevel): RedirectResponse
    {
        $this->ensureScope($userLevel);
        $this->fillLevel($userLevel, $request);
        $userLevel->save();

        flashMessage('success', config('user_levels.types.'.$this->levelType().'.singular').' updated successfully.');

        return redirect()->route($this->routeBase().'.index');
    }

    public function destroy(UserLevel $userLevel)
    {
        $this->ensureScope($userLevel);

        if ($userLevel->assignments()->exists()) {
            flashMessage('danger', 'This level is already assigned to users and cannot be deleted.');
            return back();
        }

        $userLevel->delete();
        flashMessage('success', config('user_levels.types.'.$this->levelType().'.singular').' deleted successfully.');

        return redirect()->route($this->routeBase().'.index');
    }

    public function toggle(UserLevel $userLevel)
    {
        $this->ensureScope($userLevel);
        $userLevel->status = $userLevel->status === 'Active' ? 'Inactive' : 'Active';
        $userLevel->save();

        flashMessage('success', config('user_levels.types.'.$this->levelType().'.singular').' status updated.');

        return back();
    }

    /**
     * Adopt an admin level for the current company.  A new company-owned
     * row is created that points to the admin level via
     * `inherits_from_id`; only the overrides supplied by the company
     * are persisted, so the admin defaults flow through.
     */
    public function inheritFromAdmin(Request $request, int $adminLevelId): RedirectResponse
    {
        $this->guardAdminOnlyMode();

        $companyId = $this->companyId();
        if (! $companyId) {
            flashMessage('danger', 'Only a SaaS company can inherit an admin level.');
            return back();
        }

        $adminLevel = $this->companyLevelResolver->inheritableAdminLevels($this->levelType())
            ->firstWhere('id', $adminLevelId);
        if (! $adminLevel) {
            flashMessage('danger', 'Admin level not found or no longer available.');
            return back();
        }

        $overrides = [
            'reward_config' => array_filter([
                'reward_type' => $request->input('reward_type'),
                'reward_amount' => $request->input('reward_amount'),
            ], fn ($v) => $v !== null && $v !== ''),
            'target_config' => array_filter([
                'minimum_completed_trips' => $request->input('minimum_completed_trips'),
                'minimum_earnings' => $request->input('minimum_earnings'),
                'minimum_spend' => $request->input('minimum_spend'),
                'maximum_cancellation_rate' => $request->input('maximum_cancellation_rate'),
                'minimum_rating' => $request->input('minimum_rating'),
                'minimum_reviews' => $request->input('minimum_reviews'),
            ], fn ($v) => $v !== null && $v !== ''),
        ];

        $this->companyLevelResolver->inherit($companyId, $adminLevelId, $overrides);

        flashMessage('success', 'Admin level adopted.  You can now override the values for your company.');

        return redirect()->route($this->routeBase().'.index');
    }

    /**
     * Drop the inheritance link and keep the company's own level row.
     * From this point on the company is fully responsible for the
     * values.
     */
    public function unlinkFromAdmin(UserLevel $userLevel): RedirectResponse
    {
        $this->ensureScope($userLevel);

        if (! $userLevel->inherits_from_id) {
            flashMessage('warning', 'This level is not currently inherited from an admin level.');
            return back();
        }

        $this->companyLevelResolver->unlink((int) $userLevel->company_id, (int) $userLevel->id);

        flashMessage('success', 'Inheritance removed.  The level now uses the company-specific values.');

        return redirect()->route($this->routeBase().'.index');
    }

    /**
     * Block all write actions when the platform owner is in `admin`
     * mode – the only legitimate mutation is via the admin UI itself.
     */
    protected function guardAdminOnlyMode(): void
    {
        if ($this->isCompanyScope() && $this->companyLevelResolver->isAdminMode()) {
            abort(403, 'Per-company level configuration is disabled.  Enable "Company" mode in Admin Site Settings first.');
        }
    }

    protected function fillLevel(UserLevel $level, StoreUserLevelRequest $request): void
    {
        // Companies must always have company_id set; the admin scope
        // keeps the value at null.
        $level->company_id = $this->companyId();
        $level->level_type = $this->levelType();
        $level->sequence = (int) $request->sequence;
        $level->name = $request->name;
        $level->slug = Str::slug($request->name);
        $level->icon = $request->icon;
        $level->description = $request->description;
        $level->status = $request->status;
        $level->is_default = $request->boolean('is_default');

        // The form may submit an `inherits_from_id` value to "adopt" an
        // admin level – if it is empty we explicitly clear the link
        // so the company can detach a previously inherited row.
        $inheritsFromId = $request->input('inherits_from_id');
        $level->inherits_from_id = $inheritsFromId !== null && $inheritsFromId !== '' ? (int) $inheritsFromId : null;

        // For the reward / target configs we only persist the values
        // the user actually submitted.  This means a company that
        // blanks a field will revert to the admin default.  That is
        // intentional – the requirement is "set their own values,
        // inherit the rest".
        $rewardConfig = [
            'reward_type' => $request->reward_type,
            'reward_amount' => $request->reward_amount,
        ];
        $targetConfig = [
            'minimum_completed_trips' => $request->minimum_completed_trips,
            'minimum_earnings' => $request->minimum_earnings,
            'minimum_spend' => $request->minimum_spend,
            'maximum_cancellation_rate' => $request->maximum_cancellation_rate,
            'minimum_rating' => $request->minimum_rating,
            'minimum_reviews' => $request->minimum_reviews,
        ];

        if ($level->isInherited() && (! $this->hasMeaningfulOverrides($rewardConfig) || ! $this->hasMeaningfulOverrides($targetConfig))) {
            // Keep the parent's config as a base; only the user's
            // overrides are added on top.
            $parent = $level->inheritsFrom;
            if ($parent) {
                $rewardConfig = array_merge($parent->reward_config ?? [], $rewardConfig);
                $targetConfig = array_merge($parent->target_config ?? [], $targetConfig);
            }
        }

        $level->reward_config = $rewardConfig;
        $level->target_config = $targetConfig;

        if ($level->is_default) {
            UserLevel::query()
                ->where('level_type', $this->levelType())
                ->where('company_id', $this->companyId())
                ->whereKeyNot($level->id)
                ->update(['is_default' => false]);
        }
    }

    protected function ensureScope(UserLevel $userLevel): void
    {
        abort_unless(
            $userLevel->level_type === $this->levelType() && (int) $userLevel->company_id === (int) $this->companyId(),
            404
        );
    }

    protected function hasMeaningfulOverrides(array $values): bool
    {
        foreach ($values as $value) {
            if ($value === null || $value === '' || $value === 0 || $value === '0') {
                continue;
            }
            return true;
        }
        return false;
    }
}
