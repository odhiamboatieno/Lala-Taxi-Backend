<?php

namespace App\Observers;

use App\Jobs\ReindexEntityJob;
use App\Jobs\RemoveIndexEntityJob;
use App\Models\User;

/**
 * The `users` table backs both the driver and customer index types. The same
 * row is indexed (or removed) under whichever type its `user_type` maps to so
 * a role change never leaves a stale entry behind.
 */
class UserSearchObserver
{
    public function saved(User $user): void
    {
        $type = $this->type($user);
        if ($type !== null) {
            ReindexEntityJob::dispatch($type, $user->getKey());
        }
    }

    public function deleted(User $user): void
    {
        $type = $this->type($user);
        if ($type !== null) {
            RemoveIndexEntityJob::dispatch($type, $user->getKey());
        }
    }

    public function restored(User $user): void
    {
        $this->saved($user);
    }

    private function type(User $user): ?string
    {
        return match (strtolower((string) $user->user_type)) {
            'driver' => 'driver',
            'rider' => 'customer',
            default => null,
        };
    }
}
