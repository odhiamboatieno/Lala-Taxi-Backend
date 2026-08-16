<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DriverScore;
use App\Models\DriverScoreAppeal;
use App\Models\DriverScoreEvent;
use App\Services\DriverScore\DriverScoreService;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;

class DriverScoreController extends Controller
{
    public function __construct(protected DriverScoreService $driverScoreService)
    {
    }

    public function show()
    {
        $driver = JWTAuth::parseToken()->authenticate();
        $score = $this->driverScoreService->recalculateDriver($driver, ['trigger' => 'driver_score_api']);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver score loaded successfully.',
            'score' => $score ? $this->driverScoreService->scoreSummaryPayload($score) : null,
            'appeal_available' => true,
        ]);
    }

    public function history()
    {
        $driver = JWTAuth::parseToken()->authenticate();
        $score = DriverScore::where('driver_id', $driver->id)->first();

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver score history loaded successfully.',
            'history' => DriverScoreEvent::query()
                ->where('driver_id', $driver->id)
                ->latest('event_date')
                ->latest('id')
                ->limit(30)
                ->get()
                ->map(function (DriverScoreEvent $event) {
                    return [
                        'id' => $event->id,
                        'title' => $event->title,
                        'event_type' => $event->event_type,
                        'description' => $event->description,
                        'impact_score' => (float) $event->impact_score,
                        'impact_direction' => $event->impact_direction,
                        'event_date' => optional($event->event_date ?: $event->created_at)->toIso8601String(),
                        'excluded_from_scoring' => (bool) $event->excluded_from_scoring,
                        'appeal_status' => optional($event->appeals()->latest()->first())->status,
                    ];
                })
                ->values(),
            'next_calculation_at' => optional($score?->next_calculation_at)->toIso8601String(),
        ]);
    }

    public function settings()
    {
        return response()->json([
            'status_code' => '1',
            'status_message' => 'Driver score settings loaded successfully.',
            'settings' => $this->driverScoreService->publicSettingsPayload(),
        ]);
    }

    public function appeal(Request $request)
    {
        $driver = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|min:10|max:3000',
            'driver_score_event_id' => 'nullable|exists:driver_score_events,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

        $score = DriverScore::firstOrCreate(['driver_id' => $driver->id], [
            'score_percentage' => 100,
            'status' => 'Good',
            'marketplace_access_status' => 'active',
        ]);

        $event = null;
        if ($request->filled('driver_score_event_id')) {
            $event = DriverScoreEvent::where('id', $request->driver_score_event_id)
                ->where('driver_id', $driver->id)
                ->first();

            if (! $event) {
                return response()->json([
                    'status_code' => '0',
                    'status_message' => 'Score event not found for this driver.',
                ]);
            }
        }

        $appeal = DriverScoreAppeal::create([
            'driver_id' => $driver->id,
            'driver_score_id' => $score->id,
            'driver_score_event_id' => $event?->id,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        $this->driverScoreService->logEvent($driver->id, [
            'event_key' => 'appeal_submitted_'.$appeal->id,
            'source_type' => 'driver_appeal',
            'event_type' => 'appeal_submitted',
            'title' => 'Driver appeal submitted',
            'description' => 'The driver requested a human review for a score-impacting event.',
            'impact_score' => 0,
            'impact_direction' => 'neutral',
            'metadata' => [
                'appeal_id' => $appeal->id,
                'driver_score_event_id' => $event?->id,
            ],
        ]);

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Appeal submitted successfully.',
            'appeal' => [
                'id' => $appeal->id,
                'status' => $appeal->status,
                'reason' => $appeal->reason,
                'driver_score_event_id' => $appeal->driver_score_event_id,
                'created_at' => optional($appeal->created_at)->toIso8601String(),
            ],
        ]);
    }
}
