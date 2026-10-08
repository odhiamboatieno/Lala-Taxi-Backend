<?php

namespace App\Services;

use App\Jobs\ReindexAllJob;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Maintains the persisted `dashboard_search_index` table.
 *
 * The index is intentionally source-driven: every entry in
 * `config('dashboard_search.sources')` describes how to read, normalise and
 * tenant-scope one entity type. Both the bulk `search:index` command and the
 * per-record observers share this service so the two paths can never drift.
 */
class DashboardSearchIndexer
{
    /** @var array<string, string[]> */
    private array $columns = [];

    public function indexTableReady(): bool
    {
        return Schema::hasTable('dashboard_search_index');
    }

    /**
     * Queue a background rebuild when the index has gone stale. This keeps the
     * index healthy even on hosts without a scheduler container: any admin
     * search request can trigger the self-heal.
     */
    public function refreshIfStale(): void
    {
        if (!$this->indexTableReady()) {
            return;
        }
        $staleAfter = (int) config('dashboard_search.stale_after', 900);
        $builtAt = (int) Cache::get('dashboard_search:index_built_at', 0);
        if ($builtAt > 0 && (time() - $builtAt) < $staleAfter) {
            return;
        }
        // Stamp first so a burst of concurrent requests only queues one rebuild.
        Cache::put('dashboard_search:index_built_at', time(), $staleAfter * 4);
        ReindexAllJob::dispatch(false);
    }

    public function markBuilt(): void
    {
        Cache::forever('dashboard_search:index_built_at', time());
    }


    /**
     * @return array<string, array<string, mixed>>
     */
    public function sources(): array
    {
        $sources = [];
        foreach ((array) config('dashboard_search.sources', []) as $source) {
            if (!empty($source['type'])) {
                $sources[$source['type']] = $source;
            }
        }

        return $sources;
    }

    public function source(string $type): ?array
    {
        return $this->sources()[$type] ?? null;
    }

    /**
     * Rebuild the whole index. When $rebuild is true the table is truncated
     * first, guaranteeing that stale rows cannot linger.
     */
    public function syncAll(bool $rebuild = false): array
    {
        if (!$this->indexTableReady()) {
            return ['indexed' => 0, 'sources' => 0, 'skipped' => []];
        }

        if ($rebuild) {
            DB::table('dashboard_search_index')->truncate();
        }

        $indexed = 0;
        $skipped = [];
        $sources = 0;
        foreach ($this->sources() as $type => $source) {
            $count = $this->syncType($type);
            if ($count === null) {
                $skipped[] = $type;
                continue;
            }
            $sources++;
            $indexed += $count;
        }

        if ($sources > 0) {
            $this->markBuilt();
        }

        return ['indexed' => $indexed, 'sources' => $sources, 'skipped' => $skipped];
    }

    /**
     * Reindex every row for a single entity type.
     *
     * @return int|null null when the source table is not installed
     */
    public function syncType(string $type): ?int
    {
        if (!$this->indexTableReady()) {
            return null;
        }
        $source = $this->source($type);
        if ($source === null || !Schema::hasTable($source['table'])) {
            return null;
        }

        $idColumn = $source['id_column'] ?? 'id';
        $columns = $this->columns($source['table']);
        if (!in_array($idColumn, $columns, true)) {
            return null;
        }

        $selected = $this->selectColumns($source, $columns);
        $count = 0;
        DB::table($source['table'])
            ->when(in_array('deleted_at', $columns, true), fn ($query) => $query->whereNull('deleted_at'))
            ->when(!empty($source['where']), fn ($query) => $query->where($source['where']))
            ->orderBy($idColumn)
            ->chunkById(500, function (Collection $rows) use ($source, $type, $selected, $idColumn, &$count) {
                foreach ($rows as $row) {
                    $this->storeRow($source, $type, $row, $idColumn, $selected);
                    $count++;
                }
            }, $idColumn);

        return $count;
    }

    /**
     * Index (or refresh) a single entity. Missing rows are removed so a
     * hard-deleted record cannot be resurrected by the incremental path.
     */
    public function indexEntity(string $type, $id): void
    {
        if (!$this->indexTableReady() || $id === null) {
            return;
        }
        $source = $this->source($type);
        if ($source === null || !Schema::hasTable($source['table'])) {
            return;
        }
        $idColumn = $source['id_column'] ?? 'id';
        $columns = $this->columns($source['table']);
        if (!in_array($idColumn, $columns, true)) {
            return;
        }
        $selected = $this->selectColumns($source, $columns);
        $row = DB::table($source['table'])
            ->where($idColumn, $id)
            ->when(!empty($source['where']), fn ($query) => $query->where($source['where']))
            ->first();
        if ($row === null || (in_array('deleted_at', $columns, true) && $row->deleted_at !== null)) {
            $this->removeEntity($type, $id);
            return;
        }
        $this->storeRow($source, $type, $row, $idColumn, $selected);
    }

    public function removeEntity(string $type, $id): void
    {
        if (!$this->indexTableReady() || $id === null) {
            return;
        }
        DB::table('dashboard_search_index')
            ->where('entity_type', $type)
            ->where('entity_id', (string) $id)
            ->delete();
    }

    /**
     * @param  object  $row
     * @param  string[]  $selected
     */
    private function storeRow(array $source, string $type, $row, string $idColumn, array $selected): void
    {
        $id = $row->{$idColumn};
        $companyId = $this->resolveCompany($source, $row);
        $title = $this->title($source, $row, $id);
        $detail = $this->detail($source, $row);
        $scope = $source['scope'] === 'both' ? 'both' : $source['scope'];

        $attributes = [
            'entity_type' => $type,
            'entity_id' => (string) $id,
            'company_id' => $companyId,
            'title' => Str::limit($title, 250, ''),
            'description' => Str::limit('#'.$id.($detail === '' ? '' : ' · '.$detail), 180),
            'group' => (string) ($source['group'] ?? ''),
            'icon' => $source['icon'] ?? null,
            'scope' => $scope,
            'permission' => $source['permission'] ?? null,
            'feature' => $source['feature'] ?? null,
            'detail_permission' => $source['detail_permission'] ?? $source['permission'] ?? null,
            'path' => $this->path($source, $row, $id, $companyId),
            'path_admin' => $this->adminPath($source, $row, $id, $companyId),
            'list_path' => $source['list_path'] ?? null,
            'fallback_search' => $this->fallbackSearch($source, $row),
            'search_blob' => $this->searchBlob($source, $row, $title, $detail, $selected),
            'status' => $this->status($source, $row),
            'source_updated_at' => $this->sourceUpdatedAt($source, $row),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('dashboard_search_index')->updateOrInsert(
            ['entity_type' => $type, 'entity_id' => (string) $id],
            $attributes
        );
    }

    /**
     * Compare live source row counts with indexed rows per entity type so the
     * audit command and CI can flag drift long before a user notices a gap.
     *
     * @return array{ok: bool, types: array<string, array{source: int, indexed: int, diff: int, table: string}>}
     */
    public function driftReport(): array
    {
        if (!$this->indexTableReady()) {
            return ['ok' => false, 'types' => []];
        }

        $types = [];
        $ok = true;
        foreach ($this->sources() as $type => $source) {
            if (!Schema::hasTable($source['table'])) {
                continue;
            }
            $columns = $this->columns($source['table']);
            $idColumn = $source['id_column'] ?? 'id';
            if (!in_array($idColumn, $columns, true)) {
                continue;
            }
            $query = DB::table($source['table'])
                ->when(in_array('deleted_at', $columns, true), fn ($q) => $q->whereNull('deleted_at'))
                ->when(!empty($source['where']), fn ($q) => $q->where($source['where']));
            $sourceCount = (int) $query->count();
            $indexedCount = (int) DB::table('dashboard_search_index')->where('entity_type', $type)->count();
            $diff = $indexedCount - $sourceCount;
            $ok = $ok && $diff === 0;
            $types[$type] = [
                'table' => $source['table'],
                'source' => $sourceCount,
                'indexed' => $indexedCount,
                'diff' => $diff,
            ];
        }

        return ['ok' => $ok, 'types' => $types];
    }

    private function resolveCompany(array $source, $row): ?int
    {
        if (array_key_exists('company_column', $source) && $source['company_column'] === null) {
            return null;
        }
        $column = $source['company_column'] ?? 'company_id';
        if (isset($row->{$column}) && $row->{$column} !== null && $row->{$column} !== '') {
            return (int) $row->{$column};
        }
        if (!empty($source['company_via'])) {
            [$table, $foreignKey] = $source['company_via'];
            $value = $row->{$foreignKey} ?? null;
            if ($value !== null && $value !== '' && Schema::hasTable($table)) {
                $company = DB::table($table)->where('id', $value)->value('company_id');
                return $company === null ? null : (int) $company;
            }
        }

        return null;
    }

    private function title(array $source, $row, $id): string
    {
        $title = trim(implode(' ', array_filter(array_map(
            fn ($field) => $this->stringValue($row->{$field} ?? null),
            $source['title'] ?? []
        ))));

        return $title !== '' ? $title : trim(($source['group'] ?? 'Record').' #'.$id);
    }

    private function detail(array $source, $row): string
    {
        return implode(' · ', array_filter(array_map(
            fn ($field) => $this->stringValue($row->{$field} ?? null),
            $source['detail'] ?? []
        )));
    }

    private function path(array $source, $row, $id, ?int $companyId): ?string
    {
        $path = $source['path'] ?? null;
        if ($path === null || $path === '') {
            return null;
        }

        return $this->replacePathTokens($path, $id, $companyId);
    }

    private function adminPath(array $source, $row, $id, ?int $companyId): ?string
    {
        $path = $source['path_admin'] ?? null;
        if ($path === null || $path === '') {
            return null;
        }

        return $this->replacePathTokens($path, $id, $companyId);
    }

    private function replacePathTokens(string $path, $id, ?int $companyId): string
    {
        return str_replace(
            ['{id}', '{company_id}'],
            [(string) $id, (string) ($companyId ?? '')],
            $path
        );
    }

    private function fallbackSearch(array $source, $row): ?string
    {
        $field = $source['fallback'] ?? null;
        if ($field === null || !isset($row->{$field})) {
            return null;
        }
        $value = $this->stringValue($row->{$field});

        return $value !== '' ? $value : null;
    }

    private function searchBlob(array $source, $row, string $title, string $detail, array $selected): string
    {
        $parts = [$title, $detail, $source['group'] ?? '', $source['aliases'] ?? ''];
        foreach ($selected as $column) {
            $parts[] = $this->stringValue($row->{$column} ?? null);
        }

        return Str::lower(trim(preg_replace('/\s+/u', ' ', implode(' ', array_filter($parts)))));
    }

    private function status(array $source, $row): ?string
    {
        foreach (['status', 'payment_status', 'state'] as $column) {
            if (isset($row->{$column}) && $row->{$column} !== null && $row->{$column} !== '') {
                return mb_substr((string) $row->{$column}, 0, 32);
            }
        }

        return null;
    }

    private function sourceUpdatedAt(array $source, $row): ?string
    {
        $column = $source['updated'] ?? null;
        if ($column === null || !isset($row->{$column}) || !$row->{$column}) {
            return null;
        }

        return (string) $row->{$column};
    }

    /**
     * Columns that make up the free-text blob, intersected with reality so a
     * partially migrated optional module never breaks indexing.
     *
     * @param  string[]  $columns
     * @return string[]
     */
    private function selectColumns(array $source, array $columns): array
    {
        $wanted = array_unique(array_merge(
            $source['fields'] ?? [],
            $source['title'] ?? [],
            $source['detail'] ?? [],
            array_keys($source['where'] ?? []),
            array_filter([$source['updated'] ?? null, $source['fallback'] ?? null])
        ));

        return array_values(array_intersect($wanted, $columns));
    }

    /**
     * @return string[]
     */
    private function columns(string $table): array
    {
        return $this->columns[$table] ??= array_map('strtolower', Schema::getColumnListing($table));
    }

    private function stringValue($value): string
    {
        if ($value === null || is_bool($value)) {
            return '';
        }

        return trim((string) $value);
    }
}
