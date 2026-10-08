<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Read path over the persisted dashboard search index.
 *
 * Keeps the legacy response shape (results/hasMore) and extends it with
 * per-group pagination metadata so the dialog can offer "load more" while
 * record pages can deep-link to complete result sets.
 */
class DashboardSearchIndex
{
    public function search(string $term, string $scope, ?int $companyId, callable $can, callable $featureEnabled, array $options = []): array
    {
        if (!Schema::hasTable('dashboard_search_index')) {
            return $this->unavailable();
        }

        app(DashboardSearchIndexer::class)->refreshIfStale();

        if (DB::table('dashboard_search_index')->count() === 0) {
            // Cold start: the caller falls back to the live-query service.
            return $this->unavailable();
        }

        $tokens = array_values(array_filter(preg_split('/\s+/u', Str::lower(trim($term)), -1, PREG_SPLIT_NO_EMPTY)));
        if ($tokens === []) {
            return $this->emptyAvailable();
        }

        [$allowedPermissions, $allowedFeatures] = $this->accessSets($scope, $can, $featureEnabled);
        $categoryWords = $this->categoryWords($scope);
        $constraints = array_values(array_filter($tokens, fn ($token) => !in_array($token, $categoryWords, true)));

        $total = $this->filtered($scope, $companyId, $allowedPermissions, $allowedFeatures, $constraints)
            ->count();

        $groups = $this->groupTotals($scope, $companyId, $allowedPermissions, $allowedFeatures, $constraints);
        $onlyGroup = $options['only_group'] ?? null;
        if (is_string($onlyGroup) && $onlyGroup !== '') {
            if (!isset($groups[$onlyGroup])) {
                return $this->emptyAvailable();
            }
            $groups = [$onlyGroup => $groups[$onlyGroup]];
        }

        $perGroup = max(1, (int) config('dashboard_search.results_per_group', 5));
        $groupOrder = $this->groupOrder($scope);
        uksort($groups, function ($a, $b) use ($groupOrder) {
            $ra = ($position = array_search($a, $groupOrder, true)) !== false ? $position : count($groupOrder);
            $rb = ($position = array_search($b, $groupOrder, true)) !== false ? $position : count($groupOrder);

            return $ra <=> $rb ?: ($groups[$b]['total'] <=> $groups[$a]['total']);
        });

        $listUrls = $this->groupListUrls($scope);
        $results = [];
        $hasMore = false;
        foreach (array_keys($groups) as $group) {
            $offset = max(0, (int) ($options['cursors'][$group] ?? 0));
            $rows = $this->groupRows($scope, $companyId, $allowedPermissions, $allowedFeatures, $constraints, $tokens, $group, $perGroup + 1, $offset)->get();
            $groupHasMore = $rows->count() > $perGroup;
            $hasMore = $hasMore || $groupHasMore;
            foreach ($rows->take($perGroup) as $row) {
                $item = $this->toResultItem($row, $scope, $group, $can);
                if ($item !== null) {
                    $results[] = $item;
                }
            }
            $groups[$group]['hasMore'] = $groupHasMore;
            $groups[$group]['nextCursor'] = $groupHasMore ? $offset + $perGroup : null;
            $groups[$group]['list'] = $listUrls[$group] ?? null;
        }

        return [
            'results' => $results,
            'hasMore' => $hasMore,
            'total' => $total,
            'groups' => array_values($groups),
            'available' => true,
            'indexed' => true,
        ];
    }

    /**
     * @param  callable  $can
     * @param  callable  $featureEnabled
     * @return array{0: string[], 1: string[]}
     */
    private function accessSets(string $scope, callable $can, callable $featureEnabled): array
    {
        $permissions = [];
        $features = [];
        foreach ((array) config('dashboard_search.sources', []) as $source) {
            if (!$this->sourceInScope($source, $scope)) {
                continue;
            }
            if (!empty($source['permission']) && $can($source['permission'])) {
                $permissions[] = $source['permission'];
            }
            if (!empty($source['feature']) && $featureEnabled($source['feature'])) {
                $features[] = $source['feature'];
            }
        }

        return [array_values(array_unique($permissions)), array_values(array_unique($features))];
    }

    private function filtered(string $scope, ?int $companyId, array $permissions, array $features, array $constraints): Builder
    {
        $query = DB::table('dashboard_search_index')
            ->select('dashboard_search_index.*')
            ->whereIn('scope', $scope === 'company' ? ['company', 'both'] : ['admin', 'both']);

        if ($scope === 'company') {
            $companyId = $companyId !== null ? (int) $companyId : 0;
            $query->where(fn (Builder $q) => $q->where('company_id', $companyId)->orWhereNull('company_id'));
        }

        $query->where(function (Builder $q) use ($permissions) {
            $q->whereNull('permission');
            if ($permissions !== []) {
                $q->orWhereIn('permission', $permissions);
            }
        });

        $query->where(function (Builder $q) use ($features) {
            $q->whereNull('feature');
            if ($features !== []) {
                $q->orWhereIn('feature', $features);
            }
        });

        foreach ($constraints as $token) {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $token).'%';
            $query->where(function (Builder $q) use ($pattern, $token) {
                $q->whereRaw('search_blob LIKE ? ESCAPE \'!\'', [$pattern]);
                $id = ltrim($token, '#');
                if ($id !== '' && ctype_digit($id)) {
                    $q->orWhere('entity_id', $id);
                }
            });
        }

        return $query;
    }

    private function groupTotals(string $scope, ?int $companyId, array $permissions, array $features, array $constraints): array
    {
        $rows = $this->filtered($scope, $companyId, $permissions, $features, $constraints)
            ->select(['group', DB::raw('COUNT(*) AS total')])
            ->groupBy('group')
            ->get();

        $groups = [];
        foreach ($rows as $row) {
            $label = ($row->group === '' || $row->group === null) ? 'Records' : $row->group;
            $groups[$label] = ['group' => $label, 'total' => (int) $row->total, 'hasMore' => true, 'nextCursor' => 0, 'icon' => null];
        }

        return $groups;
    }

    private function groupRows(string $scope, ?int $companyId, array $permissions, array $features, array $constraints, array $tokens, string $group, int $limit, int $offset): Builder
    {
        $query = $this->filtered($scope, $companyId, $permissions, $features, $constraints)
            ->where('group', $group)
            ->limit($limit)
            ->offset($offset);

        $this->applyScore($query, $tokens);

        return $query->orderByDesc('search_score');
    }

    private function applyScore(Builder $query, array $tokens): void
    {
        $full = implode(' ', $tokens);
        $score = 'CASE WHEN LOWER(title) = ? THEN 120 WHEN LOWER(title) LIKE ? THEN 70 WHEN LOWER(title) LIKE ? THEN 35 ELSE 0 END';
        $bindings = [$full, $full.'%', '%'.$full.'%'];

        foreach ($tokens as $token) {
            $id = ltrim($token, '#');
            if ($id !== '' && ctype_digit($id)) {
                $score .= ' + CASE WHEN entity_id = ? THEN 25 ELSE 0 END';
                $bindings[] = $id;
            }
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $token).'%';
            $score .= ' + CASE WHEN LOWER(title) LIKE ? THEN 12 ELSE CASE WHEN search_blob LIKE ? THEN 5 ELSE 0 END END';
            $bindings[] = $pattern;
            $bindings[] = $pattern;
        }

        $query->addSelect(DB::raw('('.$score.') AS search_score'));
        $query->addBinding($bindings, 'select');
    }

    /**
     * @param  callable  $can
     */
    private function toResultItem(object $row, string $scope, string $group, callable $can): ?array
    {
        if ($row->permission !== null && !$can($row->permission)) {
            return null;
        }
        $detailPermission = $row->detail_permission ?? $row->permission;
        $allowDetail = $detailPermission === null || $can($detailPermission);

        $path = null;
        if ($allowDetail) {
            $path = $scope === 'admin' && !empty($row->path_admin) ? $row->path_admin : $row->path;
        }
        if (($path === null || $path === '') && $row->list_path) {
            $path = $row->list_path;
            if (!empty($row->fallback_search)) {
                $path .= '?search='.urlencode($row->fallback_search);
            }
        }
        if ($path === null || $path === '') {
            return null;
        }

        return [
            'id' => $row->entity_type.':'.$row->entity_id,
            'title' => $row->title,
            'description' => $row->description,
            'group' => $group ?: 'Records',
            'icon' => $row->icon,
            'url' => url('/'.$scope.'/'.ltrim($path, '/')),
            'kind' => 'record',
            'entity_type' => $row->entity_type,
        ];
    }

    private function groupListUrls(string $scope): array
    {
        $map = [];
        foreach ((array) config('dashboard_search.sources', []) as $source) {
            if (!$this->sourceInScope($source, $scope) || empty($source['group']) || empty($source['list_path'])) {
                continue;
            }
            if (!isset($map[$source['group']])) {
                $map[$source['group']] = url('/'.$scope.'/'.ltrim($source['list_path'], '/'));
            }
        }

        return $map;
    }

    private function sourceInScope(array $source, string $scope): bool
    {
        $sourceScope = $source['scope'] ?? 'both';

        return $sourceScope === 'both' || $sourceScope === $scope;
    }

    private function categoryWords(string $scope): array
    {
        $words = [];
        foreach ((array) config('dashboard_search.sources', []) as $source) {
            if (!$this->sourceInScope($source, $scope)) {
                continue;
            }
            foreach (array_filter([$source['group'] ?? '', $source['aliases'] ?? '']) as $text) {
                foreach (preg_split('/\s+/u', Str::lower($text), -1, PREG_SPLIT_NO_EMPTY) as $word) {
                    $words[] = $word;
                }
            }
        }

        return array_values(array_unique($words));
    }

    private function groupOrder(string $scope): array
    {
        $order = [];
        foreach ((array) config('dashboard_search.sources', []) as $source) {
            if (!$this->sourceInScope($source, $scope) || empty($source['group'])) {
                continue;
            }
            $order[] = $source['group'];
        }

        return array_values(array_unique($order));
    }

    private function unavailable(): array
    {
        return ['results' => [], 'hasMore' => false, 'groups' => [], 'available' => false, 'indexed' => false, 'total' => 0];
    }

    private function emptyAvailable(): array
    {
        return ['results' => [], 'hasMore' => false, 'groups' => [], 'available' => true, 'indexed' => true, 'total' => 0];
    }
}