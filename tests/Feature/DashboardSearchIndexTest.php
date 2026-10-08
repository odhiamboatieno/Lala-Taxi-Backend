<?php

namespace Tests\Feature;

use App\Jobs\ReindexEntityJob;
use App\Jobs\RemoveIndexEntityJob;
use App\Models\User;
use App\Observers\DashboardSearchObserver;
use App\Observers\UserSearchObserver;
use App\Services\DashboardSearchIndexer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardSearchIndexTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $migration = require database_path('migrations/2026_10_08_000003_create_dashboard_search_index_table.php');
        $migration->up();

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('company_id')->nullable();
            foreach (['first_name', 'last_name', 'email', 'mobile_number', 'user_type', 'password', 'status'] as $field) {
                $table->string($field)->default('');
            }
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
        });
        DB::table('users')->insert([
            ['id' => 11, 'company_id' => 2, 'first_name' => 'Alice', 'last_name' => 'Smith', 'email' => 'alice@example.test', 'mobile_number' => '0700123456', 'user_type' => 'Driver', 'password' => 'SECRET-ALICE'],
            ['id' => 12, 'company_id' => 3, 'first_name' => 'Alice', 'last_name' => 'Jones', 'email' => 'other@example.test', 'mobile_number' => '0700555555', 'user_type' => 'Driver', 'password' => 'SECRET-OTHER'],
            ['id' => 13, 'company_id' => 2, 'first_name' => 'Alice', 'last_name' => 'Rider', 'email' => 'rider@example.test', 'mobile_number' => '0700666666', 'user_type' => 'Rider', 'password' => 'SECRET-RIDER'],
        ]);
    }

    private function indexer(): DashboardSearchIndexer
    {
        return app(DashboardSearchIndexer::class);
    }

    public function test_migration_creates_the_index_table(): void
    {
        $this->assertTrue(Schema::hasTable('dashboard_search_index'));
        foreach (['entity_type', 'entity_id', 'company_id', 'title', 'search_blob', 'permission', 'scope', 'source_updated_at'] as $column) {
            $this->assertTrue(Schema::hasColumn('dashboard_search_index', $column), "Missing column {$column}");
        }
    }

    public function test_sync_type_respects_where_filters_and_tenant_columns(): void
    {
        $this->assertSame(2, $this->indexer()->syncType('driver'));

        $row = DB::table('dashboard_search_index')->where('entity_type', 'driver')->where('entity_id', '11')->first();
        $this->assertNotNull($row);
        $this->assertSame('Alice Smith', $row->title);
        $this->assertSame(2, (int) $row->company_id);
        $this->assertSame('edit_driver/11', $row->path);
        $this->assertSame('driver', $row->list_path);
        $this->assertSame('alice@example.test', $row->fallback_search);
        $this->assertSame('both', $row->scope);
        $this->assertStringContainsString('alice', $row->search_blob);
        $this->assertStringNotContainsString('SECRET', $row->search_blob);

        $this->assertNull(DB::table('dashboard_search_index')->where('entity_type', 'driver')->where('entity_id', '13')->first());
        $this->assertSame(1, DB::table('dashboard_search_index')->where('entity_type', 'driver')->where('company_id', 3)->count());
    }

    public function test_sync_all_with_rebuild_removes_stale_rows(): void
    {
        DB::table('dashboard_search_index')->insert([
            'entity_type' => 'ghost', 'entity_id' => '999', 'scope' => 'both', 'title' => 'Ghost',
        ]);

        $result = $this->indexer()->syncAll(true);
        $this->assertSame(0, DB::table('dashboard_search_index')->where('entity_type', 'ghost')->count());
        $this->assertGreaterThanOrEqual(2, $result['indexed']);
        $this->assertContains('driver', array_keys($this->indexer()->sources()));
    }

    public function test_index_entity_refreshes_updates_and_clears_non_matching_rows(): void
    {
        $indexer = $this->indexer();
        $indexer->indexEntity('driver', 11);
        $this->assertSame('alice@example.test', DB::table('dashboard_search_index')->where('entity_id', '11')->value('fallback_search'));

        DB::table('users')->where('id', 11)->update(['email' => 'new@example.test']);
        $indexer->indexEntity('driver', 11);
        $this->assertSame('new@example.test', DB::table('dashboard_search_index')->where('entity_id', '11')->value('fallback_search'));

        DB::table('users')->where('id', 11)->update(['user_type' => 'Rider']);
        $indexer->indexEntity('driver', 11);
        $this->assertNull(DB::table('dashboard_search_index')->where('entity_type', 'driver')->where('entity_id', '11')->first());

        DB::table('users')->where('id', 12)->update(['deleted_at' => now()]);
        $indexer->indexEntity('driver', 12);
        $this->assertNull(DB::table('dashboard_search_index')->where('entity_id', '12')->first());
    }

    public function test_remove_entity_deletes_only_that_record(): void
    {
        $indexer = $this->indexer();
        $indexer->syncType('driver');
        $indexer->removeEntity('driver', 11);
        $this->assertNull(DB::table('dashboard_search_index')->where('entity_id', '11')->first());
        $this->assertNotNull(DB::table('dashboard_search_index')->where('entity_id', '12')->first());
    }

    public function test_generic_observer_dispatches_reindex_and_remove_jobs(): void
    {
        Queue::fake();
        $observer = new DashboardSearchObserver('driver');
        $model = new User();
        $model->user_type = 'Driver';
        $model->id = 55;

        $observer->saved($model);
        Queue::assertPushed(ReindexEntityJob::class, fn ($job) => $job->entityType === 'driver' && $job->entityId === 55);

        $observer->deleted($model);
        Queue::assertPushed(RemoveIndexEntityJob::class, fn ($job) => $job->entityType === 'driver' && $job->entityId === 55);
    }

    public function test_user_observer_maps_user_type_to_entity_type(): void
    {
        Queue::fake();
        $observer = new UserSearchObserver();

        $driver = new User();
        $driver->user_type = 'Driver';
        $driver->id = 1;
        $observer->saved($driver);
        Queue::assertPushed(ReindexEntityJob::class, fn ($job) => $job->entityType === 'driver');

        $rider = new User();
        $rider->user_type = 'Rider';
        $rider->id = 2;
        $observer->saved($rider);
        Queue::assertPushed(ReindexEntityJob::class, fn ($job) => $job->entityType === 'customer');

        $adminLike = new User();
        $adminLike->user_type = 'Admin';
        $adminLike->id = 3;
        Queue::assertNotPushed(ReindexEntityJob::class, fn ($job) => $job->entityId === 3);
    }

    public function test_search_index_command_builds_the_index(): void
    {
        $this->artisan('search:index')
            ->expectsOutputToContain('Indexed')
            ->assertExitCode(0);

        $this->artisan('search:index', ['--type' => 'driver'])
            ->expectsOutputToContain("Indexed 2 driver record(s).")
            ->assertExitCode(0);

        $this->artisan('search:index', ['--type' => 'missing_type'])
            ->assertExitCode(1);
    }

    public function test_indexer_is_a_safe_no_op_without_the_table(): void
    {
        Schema::drop('dashboard_search_index');
        $this->assertSame(['indexed' => 0, 'sources' => 0, 'skipped' => []], $this->indexer()->syncAll());
        $this->assertNull($this->indexer()->syncType('driver'));
    }

    public function test_drift_report_flags_out_of_sync_types(): void
    {
        $indexer = $this->indexer();
        $indexer->syncType('driver');
        $report = $indexer->driftReport();
        $this->assertSame(0, $report['types']['driver']['diff']);
        $this->assertSame(2, $report['types']['driver']['indexed']);
        $this->assertSame(2, $report['types']['driver']['source']);

        DB::table('users')->insert(['company_id' => 2, 'first_name' => 'Alice', 'last_name' => 'New', 'user_type' => 'Driver']);
        $stale = $indexer->driftReport();
        $this->assertSame(-1, $stale['types']['driver']['diff']);

        $indexer->syncType('driver');
        $this->assertSame(0, $indexer->driftReport()['types']['driver']['diff']);
    }

    public function test_audit_command_reports_and_fixes_drift(): void
    {
        $this->indexer()->syncAll(false);

        $this->artisan('search:index:audit')
            ->expectsOutputToContain('Index is in sync.')
            ->assertExitCode(0);

        DB::table('users')->insert(['company_id' => 2, 'first_name' => 'Alice', 'last_name' => 'New', 'user_type' => 'Driver']);
        $this->artisan('search:index:audit')
            ->expectsOutputToContain('DRIFT driver')
            ->assertExitCode(1);

        $this->artisan('search:index:audit', ['--fix' => true])
            ->expectsOutputToContain('Drift resolved.')
            ->assertExitCode(0);
    }

    public function test_config_sources_are_well_formed(): void
    {
        $sources = config('dashboard_search.sources');
        $this->assertNotEmpty($sources);
        $types = [];
        foreach ($sources as $source) {
            $this->assertIsString($source['type'] ?? null);
            $this->assertNotSame('', $source['type'] ?? '');
            $this->assertIsString($source['table'] ?? null);
            $this->assertNotSame('', $source['table'] ?? '');
            $this->assertContains($source['scope'] ?? null, ['both', 'admin', 'company']);
            $this->assertIsString($source['group'] ?? null);
            $this->assertNotSame('', $source['group'] ?? '');
            foreach (['permission', 'detail_permission', 'feature', 'aliases', 'path', 'path_admin', 'list_path'] as $nullable) {
                $this->assertTrue(!array_key_exists($nullable, $source) || $source[$nullable] === null || is_string($source[$nullable]), "{$source['type']}.{$nullable}");
            }
            foreach (['title', 'detail', 'fields'] as $list) {
                $this->assertIsArray($source[$list] ?? [], "{$source['type']}.{$list}");
            }
            $types[] = $source['type'];
        }
        $this->assertCount(count($types), array_unique($types));
    }
}
