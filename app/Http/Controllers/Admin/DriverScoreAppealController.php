<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DriverScoreAppeal;
use App\Services\DriverScore\DriverScoreService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class DriverScoreAppealController extends Controller
{
    public function __construct(protected DriverScoreService $driverScoreService)
    {
    }

    public function index(Request $request)
    {
        $appeals = DriverScoreAppeal::with(['driver', 'event'])
            ->when($request->status, fn ($query) => $query->where('status', $request->status))
            ->latest('id')
            ->paginate(25);

        return view('admin.driver_scores.appeals', [
            'appeals' => $appeals,
            'status' => $request->status,
        ]);
    }

    public function show($id)
    {
        return view('admin.driver_scores.appeal_show', [
            'appeal' => DriverScoreAppeal::with(['driver', 'event', 'score'])->findOrFail($id),
        ]);
    }

    public function update(Request $request, $id)
    {
        $appeal = DriverScoreAppeal::with(['driver', 'event'])->findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'admin_notes' => 'required|string|max:3000',
            'restore_event_impact' => 'nullable',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $appeal->status = $request->status;
        $appeal->admin_notes = $request->admin_notes;
        $appeal->reviewed_by = Auth::guard('admin')->id();
        $appeal->reviewed_at = now();
        $appeal->resolution_metadata = array_merge($appeal->resolution_metadata ?? [], [
            'restore_event_impact' => $request->boolean('restore_event_impact'),
        ]);
        $appeal->save();

        if ($request->status === 'approved' && $request->boolean('restore_event_impact') && $appeal->event) {
            $appeal->event->excluded_from_scoring = true;
            $appeal->event->metadata = array_merge($appeal->event->metadata ?? [], [
                'appeal_restored' => true,
                'appeal_id' => $appeal->id,
            ]);
            $appeal->event->save();
        }

        $this->driverScoreService->logEvent($appeal->driver_id, [
            'event_key' => 'appeal_reviewed_'.$appeal->id,
            'source_type' => 'admin',
            'event_type' => 'appeal_'.$request->status,
            'title' => 'Driver score appeal '.ucfirst($request->status),
            'description' => $request->admin_notes,
            'impact_score' => 0,
            'impact_direction' => 'neutral',
            'metadata' => [
                'appeal_id' => $appeal->id,
                'restore_event_impact' => $request->boolean('restore_event_impact'),
            ],
        ]);

        flashMessage('success', 'Appeal reviewed successfully.');

        return redirect('admin/driver-score/appeals/'.$appeal->id);
    }
}
