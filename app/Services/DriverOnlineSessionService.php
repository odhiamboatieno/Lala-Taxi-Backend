<?php

namespace App\Services;

use App\Models\DriverOnlineSession;
use App\Models\User;
use Carbon\Carbon;

class DriverOnlineSessionService
{
    public function currentOpenSession(User $driver): ?DriverOnlineSession
    {
        return DriverOnlineSession::where('driver_id', $driver->id)
            ->where('status', 'open')
            ->latest('id')
            ->first();
    }

    public function ensureOpenSession(User $driver, array $meta = []): DriverOnlineSession
    {
        return $this->currentOpenSession($driver) ?: $this->startSession($driver, $meta);
    }

    public function startSession(User $driver, array $meta = []): DriverOnlineSession
    {
        $existing = $this->currentOpenSession($driver);
        if ($existing) {
            return $existing;
        }

        return DriverOnlineSession::create([
            'driver_id' => $driver->id,
            'started_at' => now(),
            'ended_at' => null,
            'total_seconds' => 0,
            'status' => 'open',
            'start_lat' => $meta['start_lat'] ?? $meta['lat'] ?? $driver->current_lat,
            'start_lng' => $meta['start_lng'] ?? $meta['lng'] ?? $driver->current_lng,
            'app_version' => $meta['app_version'] ?? null,
            'device_id' => $meta['device_id'] ?? null,
        ]);
    }

    public function endSession(User $driver, array $meta = []): ?DriverOnlineSession
    {
        $session = $this->currentOpenSession($driver);
        if (! $session) {
            return null;
        }

        $endedAt = now();
        $session->forceFill([
            'ended_at' => $endedAt,
            'total_seconds' => max(0, $session->started_at->diffInSeconds($endedAt)),
            'status' => 'closed',
            'end_lat' => $meta['end_lat'] ?? $meta['lat'] ?? $driver->current_lat,
            'end_lng' => $meta['end_lng'] ?? $meta['lng'] ?? $driver->current_lng,
        ])->save();

        return $session->fresh();
    }

    public function buildStatus(User $driver): array
    {
        $timezone = config('app.timezone');
        $now = now();
        $openSession = $this->currentOpenSession($driver);
        $todayStart = Carbon::now($timezone)->startOfDay()->timezone($timezone);
        $weekStart = Carbon::now($timezone)->startOfWeek()->timezone($timezone);
        $monthStart = Carbon::now($timezone)->startOfMonth()->timezone($timezone);

        return [
            'is_online' => (bool) $driver->is_online && $openSession !== null,
            'current_session_started_at' => optional($openSession?->started_at)->toIso8601String(),
            'current_session_seconds' => $openSession ? max(0, $openSession->started_at->diffInSeconds($now)) : 0,
            'today_online_seconds' => $this->sumSecondsWithinRange($driver->id, $todayStart, $now),
            'week_online_seconds' => $this->sumSecondsWithinRange($driver->id, $weekStart, $now),
            'month_online_seconds' => $this->sumSecondsWithinRange($driver->id, $monthStart, $now),
            'recent_sessions' => DriverOnlineSession::where('driver_id', $driver->id)
                ->latest('started_at')
                ->limit(20)
                ->get()
                ->map(fn (DriverOnlineSession $session) => [
                    'id' => $session->id,
                    'started_at' => optional($session->started_at)->toIso8601String(),
                    'ended_at' => optional($session->ended_at)->toIso8601String(),
                    'total_seconds' => $session->status === 'open'
                        ? max(0, $session->started_at->diffInSeconds($now))
                        : (int) $session->total_seconds,
                    'status' => $session->status,
                ])
                ->values()
                ->all(),
        ];
    }

    protected function sumSecondsWithinRange(int $driverId, Carbon $from, Carbon $to): int
    {
        $sessions = DriverOnlineSession::where('driver_id', $driverId)
            ->where('started_at', '<=', $to)
            ->where(function ($query) use ($from) {
                $query->whereNull('ended_at')->orWhere('ended_at', '>=', $from);
            })
            ->get();

        $seconds = 0;
        foreach ($sessions as $session) {
            $sessionStart = $session->started_at->copy();
            $sessionEnd = ($session->ended_at ?: now())->copy();
            $effectiveStart = $sessionStart->greaterThan($from) ? $sessionStart : $from->copy();
            $effectiveEnd = $sessionEnd->lessThan($to) ? $sessionEnd : $to->copy();
            if ($effectiveEnd->greaterThan($effectiveStart)) {
                $seconds += $effectiveStart->diffInSeconds($effectiveEnd);
            }
        }

        return $seconds;
    }
}
