<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverScore;
use App\Models\DriverScoreEvent;
use App\Models\DriverScoreSetting;
use App\Models\User;
use App\Services\DriverScore\DriverScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class DriverScoreController extends Controller
{
    public function __construct(protected DriverScoreService $driverScoreService)
    {
    }

    public function index(Request $request)
    {
        $query = DriverScore::with('driver')->latest('score_percentage');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->marketplace_access_status) {
            $query->where('marketplace_access_status', $request->marketplace_access_status);
        }

        if ($request->search) {
            $search = trim($request->search);
            $query->whereHas('driver', function ($driverQuery) use ($search) {
                $driverQuery->where('first_name', 'like', '%'.$search.'%')
                    ->orWhere('last_name', 'like', '%'.$search.'%')
                    ->orWhere('mobile_number', 'like', '%'.$search.'%');
            });
        }

        return view('admin.driver_scores.index', [
            'scores' => $query->paginate(25),
            'filters' => $request->only(['status', 'marketplace_access_status', 'search']),
        ]);
    }

    public function show($driverId)
    {
        $driver = User::where('id', $driverId)->where('user_type', 'Driver')->firstOrFail();
        $score = $this->driverScoreService->recalculateDriver($driver, ['trigger' => 'admin_view']);
        $events = DriverScoreEvent::where('driver_id', $driver->id)->latest('event_date')->latest('id')->paginate(20);

        return view('admin.driver_scores.show', [
            'driver' => $driver,
            'score' => $score,
            'payload' => $score ? $this->driverScoreService->scoreSummaryPayload($score) : null,
            'events' => $events,
        ]);
    }

    public function settings()
    {
        return view('admin.driver_scores.settings', [
            'settings' => DriverScoreSetting::query()->orderBy('group')->orderBy('id')->get()->groupBy('group'),
        ]);
    }

    public function updateSettings(Request $request)
    {
        $rules = [];
        foreach (DriverScoreSetting::all() as $setting) {
            $rules[$setting->key] = match ($setting->type) {
                'int' => 'required|integer',
                'float' => 'required|numeric',
                'bool' => 'nullable',
                default => 'nullable|string',
            };
        }

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        foreach (DriverScoreSetting::all() as $setting) {
            $value = $request->input($setting->key);
            if ($setting->type === 'bool') {
                $value = $request->boolean($setting->key) ? 'true' : 'false';
            }

            $setting->value = (string) $value;
            $setting->save();
        }

        flashMessage('success', 'Driver score settings updated successfully.');

        return redirect()->back();
    }

    public function excludeEvent(Request $request, $eventId)
    {
        $event = DriverScoreEvent::findOrFail($eventId);
        $event->excluded_from_scoring = true;
        $event->metadata = array_merge($event->metadata ?? [], [
            'excluded_note' => $request->note,
            'excluded_by' => Auth::guard('admin')->id(),
        ]);
        $event->save();

        $this->driverScoreService->recalculateDriver($event->driver_id, ['trigger' => 'event_excluded']);
        flashMessage('success', 'Score event excluded from scoring.');

        return back();
    }

    public function restoreEvent(Request $request, $eventId)
    {
        $event = DriverScoreEvent::findOrFail($eventId);
        $event->excluded_from_scoring = false;
        $event->metadata = array_merge($event->metadata ?? [], [
            'restored_note' => $request->note,
            'restored_by' => Auth::guard('admin')->id(),
        ]);
        $event->save();

        $this->driverScoreService->recalculateDriver($event->driver_id, ['trigger' => 'event_restored']);
        flashMessage('success', 'Score event impact restored.');

        return back();
    }

    public function overrideMarketplaceAccess(Request $request, $driverId)
    {
        $validator = Validator::make($request->all(), [
            'marketplace_access_status' => 'required|in:active,limited,review_required,suspended',
            'note' => 'nullable|string|max:1000',
            'override_active' => 'nullable',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $driver = User::where('id', $driverId)->where('user_type', 'Driver')->firstOrFail();
        $score = $this->driverScoreService->recalculateDriver($driver, ['trigger' => 'manual_override_prepare']);
        $metadata = $score->metadata ?? [];
        $metadata['manual_access_override_active'] = $request->boolean('override_active');
        $metadata['manual_access_override_status'] = $request->marketplace_access_status;
        $metadata['manual_access_override_note'] = $request->note;
        $metadata['manual_access_override_by'] = Auth::guard('admin')->id();
        $metadata['manual_access_override_at'] = now()->toIso8601String();
        $score->marketplace_access_status = $request->marketplace_access_status;
        $score->metadata = $metadata;
        $score->save();

        $this->driverScoreService->logEvent($driver->id, [
            'source_type' => 'admin',
            'event_type' => 'admin_marketplace_override',
            'title' => 'Marketplace access updated manually',
            'description' => $request->note,
            'impact_score' => 0,
            'impact_direction' => 'neutral',
            'metadata' => [
                'marketplace_access_status' => $request->marketplace_access_status,
                'override_active' => $request->boolean('override_active'),
                'admin_id' => Auth::guard('admin')->id(),
            ],
        ], false);

        flashMessage('success', 'Marketplace access status updated successfully.');

        return back();
    }

    public function recalculate($driverId)
    {
        $driver = User::where('id', $driverId)->where('user_type', 'Driver')->firstOrFail();
        $this->driverScoreService->recalculateDriver($driver, ['trigger' => 'manual_admin_recalculation']);
        flashMessage('success', 'Driver score recalculated successfully.');

        return back();
    }
}
