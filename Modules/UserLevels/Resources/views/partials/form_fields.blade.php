@php
    $targetConfig = old() ? [
        'minimum_completed_trips' => old('minimum_completed_trips'),
        'minimum_earnings' => old('minimum_earnings'),
        'minimum_spend' => old('minimum_spend'),
        'maximum_cancellation_rate' => old('maximum_cancellation_rate'),
        'minimum_rating' => old('minimum_rating'),
        'minimum_reviews' => old('minimum_reviews'),
    ] : ($result->target_config ?? []);
    $rewardConfig = old() ? [
        'reward_type' => old('reward_type'),
        'reward_amount' => old('reward_amount'),
    ] : ($result->reward_config ?? []);

    // Pre-fill from the inherited level so that the form is friendly to
    // companies that just want to override a single value.
    $parent = $result->inheritsFrom ?? null;
    $parentTarget = $parent?->target_config ?? [];
    $parentReward = $parent?->reward_config ?? [];
    $resolvedTarget = array_merge($parentTarget, $targetConfig);
    $resolvedReward = array_merge($parentReward, $rewardConfig);

    // When showing the actual <input> values we still display the
    // company's stored value (which may be empty when the company did
    // not override the field) so the form round-trips correctly.  A
    // small "from admin" hint surfaces the inherited default.
    $isCompanyScope = defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE === 'company';
@endphp

<div class="row g-4">
    <div class="col-xl-8">
        @if($isCompanyScope && !empty($inheritableAdminLevels))
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <p class="text-uppercase text-muted small mb-1">Inherit From Admin Level</p>
                    <h5 class="mb-1">Use an admin-configured level as your base</h5>
                    <p class="text-muted mb-3">
                        Select an admin level to inherit.  Anything you leave blank will continue to use the admin's value,
                        so you only have to override the thresholds and rewards that matter for your company.
                    </p>
                    <div class="row g-3 align-items-end">
                        <div class="col-md-9">
                            <label class="form-label">Admin Level</label>
                            <select class="form-select" name="inherits_from_id">
                                <option value="">— No inheritance, define everything yourself —</option>
                                @foreach($inheritableAdminLevels as $adminLevel)
                                    <option value="{{ $adminLevel->id }}" {{ (string) old('inherits_from_id', $result->inherits_from_id ?? '') === (string) $adminLevel->id ? 'selected' : '' }}>
                                        {{ $adminLevel->name }} ({{ $adminLevel->sequence }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            @if($result->exists && $result->inherits_from_id)
                                <form method="POST" action="{{ route('userlevels.' . $type . '-levels.unlink', $result) }}" onsubmit="return confirm('Drop the inheritance?  Only the values stored on this level will remain.');">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-warning w-100">Unlink from admin</button>
                                </form>
                            @endif
                        </div>
                    </div>
                    @if($parent)
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="ri-information-line me-1"></i>
                            Currently inheriting <strong>{{ $parent->name }}</strong>.  Any field you leave blank inherits the admin value.
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
                    <div>
                        <p class="text-uppercase text-muted small mb-1">Current Level Setup</p>
                        <h4 class="mb-1">{{ $title }}</h4>
                        <p class="text-muted mb-0">Configure the visual tier, promotion targets, and reward logic for this level.</p>
                    </div>
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_default" value="1" id="is_default" {{ old('is_default', $result->is_default) ? 'checked' : '' }}>
                        <label class="form-check-label" for="is_default">Default level</label>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Level Sequence <em class="text-danger">*</em></label>
                        <input type="number" class="form-control" name="sequence" min="1" value="{{ old('sequence', $result->sequence) }}">
                        <span class="text-danger">{{ $errors->first('sequence') }}</span>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label">Level Name <em class="text-danger">*</em></label>
                        <input type="text" class="form-control" name="name" value="{{ old('name', $result->name) }}" placeholder="Ex: Gold, Loyal User, Power Customer">
                        <span class="text-danger">{{ $errors->first('name') }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Status <em class="text-danger">*</em></label>
                        <select class="form-select" name="status">
                            @foreach(['Active', 'Inactive'] as $status)
                                <option value="{{ $status }}" {{ old('status', $result->status) === $status ? 'selected' : '' }}>{{ $status }}</option>
                            @endforeach
                        </select>
                        <span class="text-danger">{{ $errors->first('status') }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Level Icon</label>
                        <input type="text" class="form-control" name="icon" value="{{ old('icon', $result->icon) }}" placeholder="ri-steering-2-line">
                        @if($parent?->icon)
                            <small class="text-muted">Inherited from admin: <code>{{ $parent->icon }}</code> (leave blank to use it).</small>
                        @else
                            <small class="text-muted">Use any icon class already available in your theme.</small>
                        @endif
                    </div>
                    <div class="col-12">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3" placeholder="Short explanation for admins and operators.">{{ old('description', $result->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body p-4">
                <div class="mb-4">
                    <p class="text-uppercase text-muted small mb-1">Reward Setup</p>
                    <h5 class="mb-1">Deserving reward for completing this level</h5>
                    <p class="text-muted mb-0">Optional reward applied when the user reaches the targets for this tier.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Reward Type</label>
                        <select class="form-select" name="reward_type">
                            <option value="">Select reward type</option>
                            @foreach(['bonus', 'cash', 'coupon', 'priority_dispatch', 'badge_only'] as $rewardType)
                                <option value="{{ $rewardType }}" {{ ($rewardConfig['reward_type'] ?? '') === $rewardType ? 'selected' : '' }}>{{ ucwords(str_replace('_', ' ', $rewardType)) }}</option>
                            @endforeach
                        </select>
                        @if($parent?->reward_config['reward_type'] ?? false)
                            <small class="text-muted">Inherited from admin: <code>{{ $parent->reward_config['reward_type'] }}</code></small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Reward Amount</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="reward_amount" value="{{ $rewardConfig['reward_amount'] ?? '' }}" placeholder="{{ $parent?->reward_config['reward_amount'] ?? '0.00' }}">
                        @if($parent?->reward_config['reward_amount'] ?? false)
                            <small class="text-muted">Inherited from admin: <code>{{ $parent->reward_config['reward_amount'] }}</code> (leave blank to use it).</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-body p-4">
                <div class="mb-4">
                    <p class="text-uppercase text-muted small mb-1">Promotion Targets</p>
                    <h5 class="mb-1">Set target to promote from this level</h5>
                    <p class="text-muted mb-0">Users who meet all populated targets can progress into this tier automatically.</p>
                </div>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Minimum Completed Trips</label>
                        <input type="number" min="0" class="form-control" name="minimum_completed_trips" value="{{ $targetConfig['minimum_completed_trips'] ?? '' }}" placeholder="{{ $parentTarget['minimum_completed_trips'] ?? '' }}">
                        @if($parentTarget['minimum_completed_trips'] ?? false)
                            <small class="text-muted">Inherited from admin: <code>{{ $parentTarget['minimum_completed_trips'] }}</code></small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $type === 'driver' ? 'Minimum Earnings' : 'Minimum Spend' }}</label>
                        <input type="number" step="0.01" min="0" class="form-control" name="{{ $type === 'driver' ? 'minimum_earnings' : 'minimum_spend' }}" value="{{ $type === 'driver' ? ($targetConfig['minimum_earnings'] ?? '') : ($targetConfig['minimum_spend'] ?? '') }}" placeholder="{{ $type === 'driver' ? ($parentTarget['minimum_earnings'] ?? '') : ($parentTarget['minimum_spend'] ?? '') }}">
                        @if($type === 'driver' ? ($parentTarget['minimum_earnings'] ?? false) : ($parentTarget['minimum_spend'] ?? false))
                            <small class="text-muted">Inherited from admin: <code>{{ $type === 'driver' ? $parentTarget['minimum_earnings'] : $parentTarget['minimum_spend'] }}</code></small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Maximum Cancellation Rate (%)</label>
                        <input type="number" step="0.01" min="0" max="100" class="form-control" name="maximum_cancellation_rate" value="{{ $targetConfig['maximum_cancellation_rate'] ?? '' }}" placeholder="{{ $parentTarget['maximum_cancellation_rate'] ?? '' }}">
                        @if($parentTarget['maximum_cancellation_rate'] ?? false)
                            <small class="text-muted">Inherited from admin: <code>{{ $parentTarget['maximum_cancellation_rate'] }}</code></small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Minimum Rating</label>
                        <input type="number" step="0.01" min="0" max="5" class="form-control" name="minimum_rating" value="{{ $targetConfig['minimum_rating'] ?? '' }}" placeholder="{{ $parentTarget['minimum_rating'] ?? '' }}">
                        @if($parentTarget['minimum_rating'] ?? false)
                            <small class="text-muted">Inherited from admin: <code>{{ $parentTarget['minimum_rating'] }}</code></small>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">{{ $type === 'driver' ? 'Minimum Reviews Received' : 'Minimum Reviews Given' }}</label>
                        <input type="number" min="0" class="form-control" name="minimum_reviews" value="{{ $targetConfig['minimum_reviews'] ?? '' }}" placeholder="{{ $parentTarget['minimum_reviews'] ?? '' }}">
                        @if($parentTarget['minimum_reviews'] ?? false)
                            <small class="text-muted">Inherited from admin: <code>{{ $parentTarget['minimum_reviews'] }}</code></small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <p class="text-uppercase text-muted small mb-1">Preview</p>
                <h5 class="mb-4">How this level will appear</h5>
                <div class="rounded-4 border p-4 bg-light-subtle text-center">
                    <div class="mx-auto mb-3 rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary" style="width:72px;height:72px;">
                        <i class="{{ old('icon', $result->icon ?: config('user_levels.types.'.$type.'.icon')) }} fs-2"></i>
                    </div>
                    <h4 class="mb-1">{{ old('name', $result->name ?: 'Level Name') }}</h4>
                    <div class="badge bg-success-subtle text-success mb-3">{{ old('status', $result->status ?: 'Active') }}</div>
                    <p class="text-muted mb-0">{{ old('description', $result->description ?: 'Your level description appears here for admins and company operators.') }}</p>
                </div>
                <div class="mt-4 d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Save Level</button>
                    <a href="{{ url((defined('LOGIN_USER_TYPE') && LOGIN_USER_TYPE === 'company') ? 'company' : 'admin').'/'.($type === 'driver' ? 'driver-levels' : 'customer-levels') }}" class="btn btn-light">Cancel</a>
                </div>
            </div>
        </div>
    </div>
</div>
