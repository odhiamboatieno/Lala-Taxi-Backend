<?php

namespace Tests\Feature;

use App\Services\DashboardSearchIndex;
use App\Services\DashboardSearchIndexer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DashboardSearchIndexQueryTest extends TestCase
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
            ['id' => 11, 'company_id' => 2, 'first_name' => 'Alice', 'last_name' => 'Smith', 'email' => 'alice@example.test', 'mobile_number' => '0700123456', 'user_type' => 'Driver', 'password' => 'SECRET'],
            ['id' => 12, 'company_id' => 3, 'first_name' => 'Alice', 'last_name' => 'Jones', 'email' => 'other@example.test', 'mobile_number' => '0700555555', 'user_type' => 'Driver', 'password' => 'SECRET'],
            ['id' => 13, 'company_id' => 2, 'first_name' => 'Alice', 'last_name' => 'Rider', 'email' => 'rider@example.test', 'mobile_number' => '0700666666', 'user_type' => 'Rider', 'password' => 'SECRET'],
        ]);

        app(DashboardSearchIndexer::class)->syncAll(false);
        Cache::put('dashboard_search:index_built_at', time(), 3600);
    }

    private function search(string $term, string $scope = 'company', ?int $companyId = 2, array $permissions = ['view_driver', 'view_rider', 'update_driver', 'update_rider'], bool $features = true, array $options = []): array
    {
        return app(DashboardSearchIndex::class)->search($term, $scope, $companyId, fn ($permission) => in_array($permission, $permissions, true), fn () => $features, $options);
    }

    public function test_company_tenant_isolation_and_url_shapes(): void
    {
        $result = $this->search('Alice');
        $this->assertTrue($result['available']);

        $rows = $this->search('Alice')['results'];
        $this->assertCount(2, $rows);
        $this->assertSame('Alice Smith', $rows[0]['title']);
        $this->assertSame('Drivers', $rows[0]['group']);
        $this->assertSame(url('company/edit_driver/11'), $rows[0]['url']);
        $this->assertSame('Customers', $rows[1]['group']);
    }

    public function test_admin_scope_sees_every_company(): void
    {
        $rows = $this->search('Alice', 'admin', null)['results'];
        $this->assertCount(3, $rows);
        $groups = array_column($rows, 'group');
        $this->assertSame(['Drivers', 'Drivers', 'Customers'], $groups);
    }

    public function test_permission_gating_and_detail_fallback_urls(): void
    {
        $viewOnly = $this->search('Alice', permissions: ['view_driver'])['results'];
        $this->assertCount(1, $viewOnly);
        $this->assertSame('Drivers', $viewOnly[0]['group']);
        $this->assertSame(url('company/driver?search=alice%40example.test'), $viewOnly[0]['url']);

        $editable = $this->search('Alice', permissions: ['view_driver', 'update_driver'])['results'];
        $this->assertSame(url('company/edit_driver/11'), $editable[0]['url']);

        $this->assertSame([], $this->search('Alice', permissions: [])['results']);
    }

    public function test_category_words_and_id_tokens_resolve(): void
    {
        $drivers = $this->search('drivers', permissions: ['view_driver'])['results'];
        $this->assertCount(1, $drivers);
        $this->assertSame('Drivers', $drivers[0]['group']);

        $byId = $this->search('#13', permissions: ['view_rider'])['results'][0] ?? null;
        $this->assertNotNull($byId);
        $this->assertSame('Alice Rider', $byId['title']);
    }

    public function test_per_group_pagination_and_cursors(): void
    {
        for ($i = 0; $i < 7; $i++) {
            DB::table('users')->insert(['company_id' => 2, 'first_name' => 'Alice', 'last_name' => "Extra {$i}", 'user_type' => 'Driver']);
        }
        app(DashboardSearchIndexer::class)->syncAll(false);

        $page = $this->search('Alice', permissions: ['view_driver']);
        $this->assertCount(5, $page['results']);
        $this->assertTrue($page['hasMore']);
        $drivers = collect($page['groups'])->firstWhere('group', 'Drivers');
        $this->assertSame(8, $drivers['total']);
        $this->assertSame(5, $drivers['nextCursor']);

        $next = $this->search('Alice', permissions: ['view_driver'], options: ['cursors' => ['Drivers' => 5]]);
        $this->assertCount(3, $next['results']);
        $this->assertFalse($next['hasMore']);
        $this->assertNull(collect($next['groups'])->firstWhere('group', 'Drivers')['nextCursor']);
    }

    public function test_single_group_paging_returns_only_that_group(): void
    {
        for ($i = 0; $i < 7; $i++) {
            DB::table('users')->insert(['company_id' => 2, 'first_name' => 'Alice', 'last_name' => "Extra {$i}", 'user_type' => 'Driver']);
        }
        app(DashboardSearchIndexer::class)->syncAll(false);

        $first = $this->search('Alice', permissions: ['view_driver']);
        $this->assertCount(5, $first['results']);

        $next = $this->search('Alice', permissions: ['view_driver'], options: ['cursors' => ['Drivers' => 5], 'only_group' => 'Drivers']);
        $this->assertCount(3, $next['results']);
        $this->assertSame([], array_diff(array_column($next['results'], 'group'), ['Drivers']));
        $this->assertFalse($next['hasMore']);
    }

    public function test_groups_expose_see_all_list_urls(): void
    {
        $rows = $this->search('Alice')['groups'];
        $drivers = collect($rows)->firstWhere('group', 'Drivers');
        $this->assertSame(url('company/driver'), $drivers['list']);
    }

    public function test_missing_or_empty_index_reports_unavailable_for_fallback(): void
    {
        Schema::drop('dashboard_search_index');
        $this->assertFalse($this->search('Alice')['available']);
    }

    public function test_legacy_search_shape_is_preserved(): void
    {
        $result = $this->search('Alice');
        foreach (['results', 'hasMore', 'groups', 'available', 'indexed'] as $key) {
            $this->assertArrayHasKey($key, $result);
        }
        foreach ($result['results'] as $row) {
            $this->assertArrayHasKey('url', $row);
            $this->assertArrayHasKey('kind', $row);
            $this->assertSame('record', $row['kind']);
        }
    }
}