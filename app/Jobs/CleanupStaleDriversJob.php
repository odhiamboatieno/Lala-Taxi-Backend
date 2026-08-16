<?php

namespace App\Jobs;

use App\Events\DriverOnlineStatusUpdated;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CleanupStaleDriversJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $cutoff = now()->subSeconds(config('dispatch.driver_stale_after_seconds', 90));

        User::query()
            ->where('user_type', 'Driver')
            ->where('is_online', true)
            ->where(function ($query) use ($cutoff) {
                $query->whereNull('last_seen_at')->orWhere('last_seen_at', '<', $cutoff);
            })
            ->get()
            ->each(function (User $driver) {
                $driver->update([
                    'is_online' => false,
                    'is_available' => false,
                ]);

                event(new DriverOnlineStatusUpdated($driver->company_id, $driver->id, [
                    'driver_id' => $driver->id,
                    'is_online' => false,
                    'is_available' => false,
                    'last_seen_at' => optional($driver->last_seen_at)->toIso8601String(),
                    'online_started_at' => null,
                ]));
            });
    }
}
