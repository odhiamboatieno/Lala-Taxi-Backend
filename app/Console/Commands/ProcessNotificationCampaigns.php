<?php

namespace App\Console\Commands;

use App\Models\NotificationCampaign;
use App\Services\Notifications\NotificationManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

class ProcessNotificationCampaigns extends Command
{
    protected $signature = 'notifications:process-campaigns';

    protected $description = 'Process scheduled notification campaigns';

    public function handle(NotificationManager $notificationManager)
    {
        if (! Schema::hasTable('notification_campaigns')) {
            return 0;
        }

        $campaigns = NotificationCampaign::query()
            ->where('is_active', true)
            ->whereNotNull('next_run_at')
            ->where('next_run_at', '<=', now())
            ->orderBy('next_run_at')
            ->get();

        foreach ($campaigns as $campaign) {
            $notificationManager->processCampaign($campaign);
        }

        return 0;
    }
}
