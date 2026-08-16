<?php

namespace App\Services\DriverScore;

use App\Models\DriverDocuments;
use App\Models\DriverScore;
use App\Models\DriverScoreAppeal;
use App\Models\DriverScoreEvent;
use App\Models\DriverScoreSetting;
use App\Models\Rating;
use App\Models\SupportIssue;
use App\Models\TripRequest;
use App\Models\Trips;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DriverScoreService
{
    /**
     * Recalculate and persist the current score snapshot for a driver.
     *
     * The formula intentionally blends multiple weighted signals so a single
     * unfair rider rating cannot wipe out marketplace access by itself.
     */
    public function recalculateDriver($driver, array $context = []): ?DriverScore
    {
        $driver = $driver instanceof User ? $driver : User::find($driver);

        if (! $driver || strtolower((string) $driver->user_type) !== 'driver') {
            return null;
        }

        $settings = $this->settings();
        $windowStart = now()->subDays((int) $settings['rolling_window_days']);
        $historyStart = now()->subDays((int) $settings['history_window_days']);

        $recentCompletedTrips = Trips::query()
            ->where('driver_id', $driver->id)
            ->where('status', 'Completed')
            ->where(function ($query) use ($windowStart) {
                $query->where('completed_at', '>=', $windowStart)
                    ->orWhere('updated_at', '>=', $windowStart);
            })
            ->get();

        $lifetimeCompletedTrips = Trips::query()
            ->where('driver_id', $driver->id)
            ->where('status', 'Completed')
            ->count();

        $recentCancelledTrips = Trips::query()
            ->where('driver_id', $driver->id)
            ->whereIn('status', ['Cancelled', 'cancelled'])
            ->where(function ($query) use ($windowStart) {
                $query->where('cancelled_at', '>=', $windowStart)
                    ->orWhere('updated_at', '>=', $windowStart);
            })
            ->count();

        $recentTripRequests = TripRequest::query()
            ->where('driver_id', $driver->id)
            ->where('created_at', '>=', $windowStart)
            ->get();

        $recentRatings = Rating::query()
            ->where('driver_id', $driver->id)
            ->where('rider_rating', '>', 0)
            ->where('created_at', '>=', $windowStart)
            ->orderBy('created_at')
            ->get();

        $recentComplaints = SupportIssue::query()
            ->whereHas('trip', function ($query) use ($driver) {
                $query->where('driver_id', $driver->id);
            })
            ->where('type', 'issue')
            ->where('created_at', '>=', $windowStart)
            ->get();

        $score = DriverScore::firstOrNew(['driver_id' => $driver->id]);

        $activeEvents = DriverScoreEvent::query()
            ->where('driver_id', $driver->id)
            ->where('excluded_from_scoring', false)
            ->where(function ($query) use ($historyStart) {
                $query->whereNull('event_date')
                    ->orWhere('event_date', '>=', $historyStart);
            })
            ->get();

        $pendingAppeals = DriverScoreAppeal::query()
            ->where('driver_id', $driver->id)
            ->where('status', 'pending')
            ->count();

        $acceptanceRate = $this->acceptanceRate($recentTripRequests);
        $completionRate = $this->completionRate($recentCompletedTrips->count(), $recentCancelledTrips);
        $ratingMetrics = $this->ratingMetrics($recentRatings, (int) $settings['soft_rating_count']);
        $complaintRate = $this->ratePerCompleted($recentComplaints->count(), $recentCompletedTrips->count());
        $lateArrivalRate = $this->lateArrivalRate($recentCompletedTrips, (int) $settings['late_arrival_grace_minutes']);
        $verificationScore = $this->verificationScore($driver);
        $safetyIncidentCount = $activeEvents->where('event_type', 'safety_incident_confirmed')->count();
        $fraudFlagCount = $activeEvents->where('event_type', 'fraud_flag_confirmed')->count();

        $weights = $this->weights($settings);
        $weightedSignals = [
            'ratings' => $ratingMetrics['score'],
            'completion' => $completionRate,
            'acceptance' => $acceptanceRate,
            'cancellations' => max(0.0, 100 - (($recentCancelledTrips / max(1, ($recentCompletedTrips->count() + $recentCancelledTrips))) * 100)),
            'complaints' => max(0.0, 100 - ($complaintRate * 100)),
            'safety' => max(0.0, 100 - min(100, $safetyIncidentCount * 30)),
            'lateness' => max(0.0, 100 - ($lateArrivalRate * 100)),
            'fraud' => max(0.0, 100 - min(100, $fraudFlagCount * 35)),
            'verification' => $verificationScore,
        ];

        $normalizedWeightTotal = array_sum($weights) ?: 1;
        $rawScore = 0.0;
        foreach ($weights as $key => $weight) {
            $rawScore += (($weightedSignals[$key] ?? 100) * $weight);
        }
        $rawScore = $rawScore / $normalizedWeightTotal;

        $maturityFactor = min(1, $lifetimeCompletedTrips / max(1, (int) $settings['minimum_completed_trips']));
        $protectedScore = 100 - ((100 - $rawScore) * $maturityFactor);

        $eventImpact = $activeEvents
            ->filter(function (DriverScoreEvent $event) {
                return ! $event->requires_admin_confirmation || $event->admin_confirmed_at;
            })
            ->sum('impact_score');

        $finalScore = max(0, min(100, round($protectedScore + $eventImpact, 2)));
        $status = $this->statusForScore($finalScore, $settings);
        $marketplaceStatus = $this->marketplaceAccessStatus(
            $finalScore,
            $status,
            $safetyIncidentCount,
            $fraudFlagCount,
            $settings,
            $score->metadata ?? []
        );

        $thresholds = $this->thresholds($settings);
        $breakdown = $this->breakdownRows($weights, $weightedSignals, $ratingMetrics, $acceptanceRate, $completionRate, $complaintRate, $lateArrivalRate, $verificationScore, $activeEvents);
        $tips = $this->improvementTips($weightedSignals, $marketplaceStatus, $ratingMetrics, $complaintRate, $lateArrivalRate, $verificationScore);
        $history = $this->recentHistory($driver->id, 8);
        $metadata = array_merge($score->metadata ?? [], [
            'thresholds' => $thresholds,
            'breakdown' => $breakdown,
            'weighted_factors' => $weights,
            'weighted_signals' => $weightedSignals,
            'bias_protection' => [
                'minimum_completed_trips' => (int) $settings['minimum_completed_trips'],
                'soft_rating_count' => (int) $settings['soft_rating_count'],
                'maturity_factor' => round($maturityFactor, 4),
                'outlier_ratings_ignored' => $ratingMetrics['outlier_count'],
                'single_rating_protection' => true,
            ],
            'improvement_tips' => $tips,
            'recent_history' => $history,
            'marketplace_explanation' => $this->marketplaceExplanation($marketplaceStatus),
            'recalculation_context' => $context,
            'document_compliance' => $verificationScore,
        ]);

        $score->fill([
            'score_percentage' => $finalScore,
            'status' => $status,
            'marketplace_access_status' => $marketplaceStatus,
            'last_calculated_at' => now(),
            'next_calculation_at' => now()->addHours((int) $settings['recalculation_interval_hours']),
            'total_completed_trips' => $lifetimeCompletedTrips,
            'total_cancelled_trips' => $recentCancelledTrips,
            'acceptance_rate' => round($acceptanceRate, 2),
            'completion_rate' => round($completionRate, 2),
            'average_rating' => round($ratingMetrics['average_rating'], 2),
            'complaint_rate' => round($complaintRate * 100, 2),
            'late_arrival_rate' => round($lateArrivalRate * 100, 2),
            'safety_incident_count' => $safetyIncidentCount,
            'fraud_flag_count' => $fraudFlagCount,
            'appeal_pending_count' => $pendingAppeals,
            'metadata' => $metadata,
        ]);
        $score->save();

        DriverScoreEvent::query()
            ->where('driver_id', $driver->id)
            ->whereNull('driver_score_id')
            ->update(['driver_score_id' => $score->id]);

        return $score->fresh(['driver']);
    }

    public function recalculateAll(): void
    {
        User::query()
            ->where('user_type', 'Driver')
            ->orderBy('id')
            ->chunk(100, function ($drivers) {
                foreach ($drivers as $driver) {
                    $this->recalculateDriver($driver, ['trigger' => 'scheduled_recalculation']);
                }
            });
    }

    public function logEvent(int $driverId, array $attributes, bool $recalculate = true): ?DriverScoreEvent
    {
        $defaults = [
            'source_type' => 'system',
            'impact_score' => 0,
            'impact_direction' => 'neutral',
            'requires_admin_confirmation' => false,
            'event_date' => now(),
            'weight_snapshot' => $this->weights($this->settings()),
        ];

        if (empty($attributes['title']) || empty($attributes['event_type'])) {
            return null;
        }

        $payload = array_merge($defaults, $attributes, ['driver_id' => $driverId]);
        $event = ! empty($payload['event_key'])
            ? DriverScoreEvent::updateOrCreate(['event_key' => $payload['event_key']], $payload)
            : DriverScoreEvent::create($payload);

        if ($recalculate) {
            $this->recalculateDriver($driverId, [
                'trigger' => 'event_logged',
                'event_type' => $payload['event_type'],
            ]);
        }

        return $event;
    }

    public function scoreSummaryPayload(DriverScore $score): array
    {
        $meta = $score->metadata ?? [];
        $thresholds = $meta['thresholds'] ?? $this->thresholds($this->settings());

        return [
            'driver_id' => $score->driver_id,
            'score_percentage' => (float) $score->score_percentage,
            'status' => $score->status,
            'marketplace_access_status' => $score->marketplace_access_status,
            'last_calculated_at' => optional($score->last_calculated_at)->toIso8601String(),
            'next_calculation_at' => optional($score->next_calculation_at)->toIso8601String(),
            'total_completed_trips' => (int) $score->total_completed_trips,
            'total_cancelled_trips' => (int) $score->total_cancelled_trips,
            'acceptance_rate' => (float) $score->acceptance_rate,
            'completion_rate' => (float) $score->completion_rate,
            'average_rating' => (float) $score->average_rating,
            'complaint_rate' => (float) $score->complaint_rate,
            'late_arrival_rate' => (float) $score->late_arrival_rate,
            'safety_incident_count' => (int) $score->safety_incident_count,
            'fraud_flag_count' => (int) $score->fraud_flag_count,
            'appeal_pending_count' => (int) $score->appeal_pending_count,
            'thresholds' => $thresholds,
            'breakdown_percentages' => $meta['breakdown'] ?? [],
            'weighted_factors' => $meta['weighted_factors'] ?? [],
            'weighted_signals' => $meta['weighted_signals'] ?? [],
            'bias_protection' => $meta['bias_protection'] ?? [],
            'improvement_tips' => $meta['improvement_tips'] ?? [],
            'recent_history' => $meta['recent_history'] ?? [],
            'marketplace_status' => [
                'status' => $score->marketplace_access_status,
                'explanation' => $meta['marketplace_explanation'] ?? $this->marketplaceExplanation($score->marketplace_access_status),
            ],
            'metadata' => $meta,
        ];
    }

    public function publicSettingsPayload(): array
    {
        $settings = static::typedSettingsFromCollection(DriverScoreSetting::publicSettings());

        return [
            'thresholds' => $this->thresholds($settings),
            'weights' => $this->weights($settings),
            'rolling_window_days' => (int) ($settings['rolling_window_days'] ?? 60),
            'minimum_completed_trips' => (int) ($settings['minimum_completed_trips'] ?? 20),
            'soft_rating_count' => (int) ($settings['soft_rating_count'] ?? 5),
            'late_arrival_grace_minutes' => (int) ($settings['late_arrival_grace_minutes'] ?? 12),
            'recalculation_interval_hours' => (int) ($settings['recalculation_interval_hours'] ?? 24),
        ];
    }

    public function settings(): array
    {
        return static::typedSettingsFromCollection(DriverScoreSetting::query()->get());
    }

    public static function typedSettingsFromCollection(Collection $settings): array
    {
        return $settings->mapWithKeys(function ($setting) {
            return [$setting->key => DriverScoreSetting::castValue($setting->value, $setting->type)];
        })->toArray();
    }

    protected function thresholds(array $settings): array
    {
        return [
            'excellent' => (float) ($settings['excellent_threshold'] ?? 90),
            'good' => (float) ($settings['good_threshold'] ?? 80),
            'warning' => (float) ($settings['warning_threshold'] ?? 70),
            'suspension' => (float) ($settings['suspension_threshold'] ?? 55),
        ];
    }

    protected function weights(array $settings): array
    {
        return [
            'ratings' => (float) ($settings['rating_weight'] ?? 24),
            'completion' => (float) ($settings['completion_weight'] ?? 18),
            'acceptance' => (float) ($settings['acceptance_weight'] ?? 14),
            'cancellations' => (float) ($settings['cancellation_weight'] ?? 10),
            'complaints' => (float) ($settings['complaint_weight'] ?? 10),
            'safety' => (float) ($settings['safety_weight'] ?? 8),
            'lateness' => (float) ($settings['lateness_weight'] ?? 6),
            'fraud' => (float) ($settings['fraud_weight'] ?? 6),
            'verification' => (float) ($settings['verification_weight'] ?? 4),
        ];
    }

    protected function acceptanceRate(Collection $requests): float
    {
        $eligible = $requests->whereIn('status', ['accepted', 'rejected', 'expired']);
        if ($eligible->isEmpty()) {
            return 100.0;
        }

        return round(($eligible->where('status', 'accepted')->count() / max(1, $eligible->count())) * 100, 2);
    }

    protected function completionRate(int $completed, int $cancelled): float
    {
        $total = $completed + $cancelled;
        if ($total <= 0) {
            return 100.0;
        }

        return round(($completed / $total) * 100, 2);
    }

    protected function ratePerCompleted(int $count, int $completed): float
    {
        if ($completed <= 0) {
            return 0.0;
        }

        return min(1.0, $count / $completed);
    }

    protected function lateArrivalRate(Collection $completedTrips, int $graceMinutes): float
    {
        if ($completedTrips->isEmpty()) {
            return 0.0;
        }

        $lateCount = $completedTrips->filter(function ($trip) use ($graceMinutes) {
            $acceptedAt = $trip->accepted_at ?: $trip->created_at;
            $startedAt = $trip->started_at ?: $trip->updated_at;

            if (! $acceptedAt || ! $startedAt) {
                return false;
            }

            return Carbon::parse($startedAt)->diffInMinutes(Carbon::parse($acceptedAt)) > $graceMinutes;
        })->count();

        return min(1.0, $lateCount / max(1, $completedTrips->count()));
    }

    protected function verificationScore(User $driver): float
    {
        $documents = DriverDocuments::query()->where('user_id', $driver->id)->get();
        if ($documents->isEmpty()) {
            return 40.0;
        }

        $approved = $documents->where('status', '1')->count();
        $ratio = $approved / max(1, $documents->count());
        $statusBonus = $driver->status === 'Active' ? 10 : 0;

        return min(100.0, round(($ratio * 90) + $statusBonus, 2));
    }

    protected function ratingMetrics(Collection $ratings, int $softCount): array
    {
        if ($ratings->isEmpty()) {
            return [
                'average_rating' => 5.0,
                'score' => 100.0,
                'outlier_count' => 0,
            ];
        }

        $allRatings = $ratings->pluck('rider_rating')->map(fn ($value) => (float) $value)->values();
        $median = $this->median($allRatings);
        $filtered = $allRatings->reject(function ($value) use ($median, $allRatings) {
            return $allRatings->count() >= 6 && $value <= 2 && ($median - $value) >= 2;
        })->values();

        $outlierCount = max(0, $allRatings->count() - $filtered->count());
        $average = $filtered->avg() ?: $allRatings->avg() ?: 5.0;

        if ($filtered->count() < $softCount) {
            $neutralBaseline = 4.6;
            $average = (($average * $filtered->count()) + ($neutralBaseline * max(0, $softCount - $filtered->count()))) / max(1, $softCount);
        }

        $score = max(0.0, min(100.0, ($average / 5) * 100));

        return [
            'average_rating' => round($average, 2),
            'score' => round($score, 2),
            'outlier_count' => $outlierCount,
        ];
    }

    protected function median(Collection $values): float
    {
        $sorted = $values->sort()->values();
        $count = $sorted->count();
        if ($count === 0) {
            return 0;
        }

        $middle = intdiv($count, 2);
        if ($count % 2 === 0) {
            return (($sorted[$middle - 1] ?? 0) + ($sorted[$middle] ?? 0)) / 2;
        }

        return (float) ($sorted[$middle] ?? 0);
    }

    protected function statusForScore(float $score, array $settings): string
    {
        $thresholds = $this->thresholds($settings);
        if ($score >= $thresholds['excellent']) {
            return 'Excellent';
        }
        if ($score >= $thresholds['good']) {
            return 'Good';
        }
        if ($score >= $thresholds['warning']) {
            return 'Warning';
        }
        if ($score >= $thresholds['suspension']) {
            return 'Critical';
        }

        return 'Disabled Risk';
    }

    protected function marketplaceAccessStatus(
        float $score,
        string $status,
        int $safetyIncidentCount,
        int $fraudFlagCount,
        array $settings,
        array $metadata = []
    ): string {
        if (($metadata['manual_access_override_active'] ?? false) && ! empty($metadata['manual_access_override_status'])) {
            return (string) $metadata['manual_access_override_status'];
        }

        $reviewThreshold = (float) ($settings['review_threshold'] ?? 70);
        $suspensionThreshold = (float) ($settings['suspension_threshold'] ?? 55);
        $scoreOnlySuspension = filter_var($settings['suspend_from_score_only'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $incidentSuspendCount = (int) ($settings['serious_incident_suspend_count'] ?? 2);
        $fraudSuspendCount = (int) ($settings['fraud_suspend_count'] ?? 2);

        if ($score < $suspensionThreshold) {
            if ($scoreOnlySuspension || $safetyIncidentCount >= $incidentSuspendCount || $fraudFlagCount >= $fraudSuspendCount) {
                return 'suspended';
            }

            return 'review_required';
        }

        if (in_array($status, ['Critical', 'Disabled Risk'], true) || $score < $reviewThreshold) {
            return 'limited';
        }

        return 'active';
    }

    protected function marketplaceExplanation(string $status): string
    {
        return match ($status) {
            'active' => 'Marketplace access is active. Strong recent performance keeps the account fully visible.',
            'limited' => 'Marketplace access is limited while some quality signals need improvement.',
            'review_required' => 'Marketplace access requires human review before stricter actions can be applied.',
            'suspended' => 'Marketplace access is suspended because configured risk rules were met.',
            default => 'Marketplace access is being evaluated.',
        };
    }

    protected function breakdownRows(
        array $weights,
        array $signals,
        array $ratingMetrics,
        float $acceptanceRate,
        float $completionRate,
        float $complaintRate,
        float $lateArrivalRate,
        float $verificationScore,
        Collection $events
    ): array {
        return [
            ['key' => 'ratings', 'label' => 'Ratings resilience', 'weight' => $weights['ratings'], 'score' => $signals['ratings'], 'detail' => 'Rolling average '.$ratingMetrics['average_rating'].'/5 with outlier protection'],
            ['key' => 'completion', 'label' => 'Trip completion', 'weight' => $weights['completion'], 'score' => $signals['completion'], 'detail' => round($completionRate, 2).'% completed'],
            ['key' => 'acceptance', 'label' => 'Acceptance rate', 'weight' => $weights['acceptance'], 'score' => $signals['acceptance'], 'detail' => round($acceptanceRate, 2).'% accepted'],
            ['key' => 'complaints', 'label' => 'Complaint control', 'weight' => $weights['complaints'], 'score' => $signals['complaints'], 'detail' => round($complaintRate * 100, 2).'% complaint rate'],
            ['key' => 'lateness', 'label' => 'On-time arrival', 'weight' => $weights['lateness'], 'score' => $signals['lateness'], 'detail' => round($lateArrivalRate * 100, 2).'% late arrival rate'],
            ['key' => 'verification', 'label' => 'Compliance status', 'weight' => $weights['verification'], 'score' => $verificationScore, 'detail' => 'Documents and account verification'],
            ['key' => 'risk_events', 'label' => 'Confirmed risk events', 'weight' => $weights['safety'] + $weights['fraud'], 'score' => max(0, min(100, 100 + $events->sum('impact_score'))), 'detail' => $events->whereIn('event_type', ['safety_incident_confirmed', 'fraud_flag_confirmed'])->count().' active confirmed events'],
        ];
    }

    protected function improvementTips(
        array $signals,
        string $marketplaceStatus,
        array $ratingMetrics,
        float $complaintRate,
        float $lateArrivalRate,
        float $verificationScore
    ): array {
        $tips = [];

        if ($signals['acceptance'] < 85) {
            $tips[] = 'Accept more nearby trips during your active hours to improve marketplace reliability.';
        }
        if ($signals['completion'] < 90) {
            $tips[] = 'Avoid cancelling after accepting unless there is a genuine safety or vehicle issue.';
        }
        if ($ratingMetrics['average_rating'] < 4.6) {
            $tips[] = 'Communicate early, confirm pickup details, and keep the vehicle and parcel handoff experience consistent.';
        }
        if ($complaintRate > 0.08) {
            $tips[] = 'Review recent complaints carefully. Repeated complaint patterns matter more than a single bad review.';
        }
        if ($lateArrivalRate > 0.12) {
            $tips[] = 'Stay online only when you are ready to move and head to pickups promptly to reduce late-arrival signals.';
        }
        if ($verificationScore < 90) {
            $tips[] = 'Keep driver and vehicle documents approved and up to date to protect score confidence.';
        }
        if ($marketplaceStatus !== 'active') {
            $tips[] = 'If you believe the score dropped unfairly, open an appeal and reference the event that affected the score.';
        }
        if (empty($tips)) {
            $tips[] = 'Maintain strong acceptance, punctual arrivals, and complaint-free trips to keep marketplace access fully active.';
        }

        return $tips;
    }

    protected function recentHistory(int $driverId, int $limit = 10): array
    {
        return DriverScoreEvent::query()
            ->where('driver_id', $driverId)
            ->latest('event_date')
            ->latest('id')
            ->limit($limit)
            ->get()
            ->map(function (DriverScoreEvent $event) {
                return [
                    'id' => $event->id,
                    'event_type' => $event->event_type,
                    'title' => $event->title,
                    'description' => $event->description,
                    'impact_score' => (float) $event->impact_score,
                    'impact_direction' => $event->impact_direction,
                    'excluded_from_scoring' => (bool) $event->excluded_from_scoring,
                    'requires_admin_confirmation' => (bool) $event->requires_admin_confirmation,
                    'admin_confirmed' => ! is_null($event->admin_confirmed_at),
                    'event_date' => optional($event->event_date ?: $event->created_at)->toIso8601String(),
                    'metadata' => $event->metadata ?? [],
                ];
            })
            ->values()
            ->all();
    }
}
