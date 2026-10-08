@php
    $searchScope = LOGIN_USER_TYPE === 'company' ? 'company' : 'admin';
    $searchUser = auth($searchScope)->user();
    $searchAccess = [];
    // Apply the actual route permissions to menus from both sidebar and header.
    foreach (Route::getRoutes() as $searchRoute) {
        if (!in_array('GET', $searchRoute->methods()) || !str_starts_with($searchRoute->uri(), $searchScope.'/') || str_contains($searchRoute->uri(), '{')) {
            continue;
        }
        $searchAllowed = (bool) $searchUser;
        foreach ($searchRoute->middleware() as $searchMiddleware) {
            if (str_starts_with($searchMiddleware, 'admin_can:')) {
                $searchPermissions = explode('|', substr($searchMiddleware, strlen('admin_can:')));
                $searchAllowed = $searchAllowed && collect($searchPermissions)->contains(fn ($permission) => $searchScope === 'company' ? operation_team_can($permission) : $searchUser->can($permission));
            }
        }
        $searchAccess[parse_url(url($searchRoute->uri()), PHP_URL_PATH)] = $searchAllowed;
    }
    $searchFeatures = [];
    foreach (config('dashboard_search.features', []) as $searchFeature) {
        $searchFeatureUrl = url($searchScope.'/'.$searchFeature['path']);
        if ($searchAccess[parse_url($searchFeatureUrl, PHP_URL_PATH)] ?? false) {
            $searchFeatures[] = [
                'url' => $searchFeatureUrl,
                'parent' => url($searchScope.'/'.$searchFeature['parent']),
                'title' => $searchFeature['title'],
                'keywords' => $searchFeature['keywords'] ?? '',
            ];
        }
    }
@endphp
<script type="application/json" id="dashboard-search-access">@json($searchAccess)</script>
<script type="application/json" id="dashboard-search-features">@json($searchFeatures)</script>
<dialog id="dashboard-search" class="dashboard-search" aria-labelledby="dashboard-search-title" data-endpoint="{{ url($searchScope . '/search') }}">
    <div class="dashboard-search-heading">
        <span class="dashboard-search-brand"><i class="bi bi-lightning-charge" aria-hidden="true"></i> Your workspace, one search</span>
        <button type="button" class="dashboard-search-close" data-search-close aria-label="Close search"><span aria-hidden="true">×</span><kbd>Esc</kbd></button>
    </div>
    <h2 id="dashboard-search-title" class="visually-hidden">Search dashboard</h2>
    <div class="dashboard-search-field">
        <i class="bi bi-search" aria-hidden="true"></i>
        <input id="dashboard-search-input" type="search" placeholder="Search menus, features, people, bookings…" maxlength="100" autocomplete="off" spellcheck="false" role="combobox" aria-label="Search menus, features and records" aria-expanded="true" aria-autocomplete="list" aria-controls="dashboard-search-results" aria-describedby="dashboard-search-status">
        <span class="dashboard-search-live">Global search</span>
    </div>
    <div class="dashboard-search-filters" role="group" aria-label="Filter search results">
        <button type="button" data-search-filter="all" aria-pressed="true">All results</button>
        <button type="button" data-search-filter="navigation" aria-pressed="false">Menus & features</button>
        <button type="button" data-search-filter="record" aria-pressed="false">Account records</button>
    </div>
    <p id="dashboard-search-status" class="dashboard-search-status" role="status" aria-live="polite"></p>
    <div id="dashboard-search-results" class="dashboard-search-results" role="listbox" aria-label="Search results"></div>
    <div class="dashboard-search-footer">
        <span><kbd>↑</kbd><kbd>↓</kbd> to navigate <kbd>↵</kbd> to open</span>
        <span data-search-scope>{{ LOGIN_USER_TYPE === 'company' ? 'Your company workspace' : 'Administrator workspace' }}</span>
    </div>
</dialog>
<script src="{{ asset('js/dashboard-search.js') }}?v={{ filemtime(public_path('js/dashboard-search.js')) }}" defer></script>
