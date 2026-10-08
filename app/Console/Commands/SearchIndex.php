<?php

namespace App\Console\Commands;

use App\Services\DashboardSearchIndexer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class SearchIndex extends Command
{
    protected $signature = 'search:index
        {--rebuild : Truncate the index before rebuilding}
        {--type= : Reindex a single entity type}';

    protected $description = 'Build or refresh the dashboard global search index';

    public function handle(DashboardSearchIndexer $indexer): int
    {
        if (!Schema::hasTable('dashboard_search_index')) {
            $this->error('The dashboard_search_index table is missing. Run migrations first.');

            return self::FAILURE;
        }

        $type = $this->option('type');
        if ($type) {
            $count = $indexer->syncType($type);
            if ($count === null) {
                $this->error("Unknown or unmigrated entity type [{$type}].");

                return self::FAILURE;
            }
            $this->info("Indexed {$count} {$type} record(s).");

            return self::SUCCESS;
        }

        $result = $indexer->syncAll((bool) $this->option('rebuild'));
        $this->info("Indexed {$result['indexed']} record(s) across {$result['sources']} source(s).");
        if ($result['skipped'] !== []) {
            $this->warn('Skipped unmigrated sources: '.implode(', ', $result['skipped']));
        }

        return self::SUCCESS;
    }
}
