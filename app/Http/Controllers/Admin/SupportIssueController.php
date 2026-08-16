<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportIssue;
use App\Models\SupportIssueMessage;
use App\Services\DriverScore\DriverScoreService;
use Illuminate\Http\Request;
use Auth;
use Validator;

class SupportIssueController extends Controller
{
    public function __construct(protected DriverScoreService $driverScoreService)
    {
    }

    public function index(Request $request)
    {
        $query = SupportIssue::with('rider', 'trip')->latest('last_message_at')->latest('id');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $data['issues'] = $query->paginate(25);
        $data['status'] = $request->status;

        return view('admin.support_issues.view', $data);
    }

    public function show($id)
    {
        $data['issue'] = SupportIssue::with('rider', 'trip', 'messages')->findOrFail($id);

        return view('admin.support_issues.detail', $data);
    }

    public function reply(Request $request, $id)
    {
        $issue = SupportIssue::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:3000',
            'status' => 'required|in:open,in_progress,waiting_rider,resolved,closed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        SupportIssueMessage::create([
            'support_issue_id' => $issue->id,
            'sender_id' => Auth::guard('admin')->id(),
            'sender_type' => 'admin',
            'message' => $request->message,
        ]);

        $issue->assigned_admin_id = Auth::guard('admin')->id();
        $issue->status = $request->status;
        $issue->last_message_at = now();
        $issue->resolved_at = $request->status === 'resolved' ? now() : null;
        $issue->save();

        $this->syncDriverScoreFromIssue($issue, $request->status);

        flashMessage('success', 'Reply sent successfully');

        return redirect('admin/support_issues/' . $issue->id);
    }

    public function updateStatus(Request $request, $id)
    {
        $issue = SupportIssue::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:open,in_progress,waiting_rider,resolved,closed',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $issue->status = $request->status;
        $issue->assigned_admin_id = Auth::guard('admin')->id();
        $issue->resolved_at = $request->status === 'resolved' ? now() : null;
        $issue->save();

        $this->syncDriverScoreFromIssue($issue, $request->status);

        flashMessage('success', 'Status updated successfully');

        return redirect('admin/support_issues/' . $issue->id);
    }

    protected function syncDriverScoreFromIssue(SupportIssue $issue, string $status): void
    {
        $trip = $issue->trip;
        if (! $trip || ! $trip->driver_id || $issue->type !== 'issue') {
            return;
        }

        if ($status === 'resolved') {
            $this->driverScoreService->logEvent($trip->driver_id, [
                'event_key' => 'support_issue_resolved_'.$issue->id,
                'trip_id' => $trip->id,
                'support_issue_id' => $issue->id,
                'source_type' => 'support_issue',
                'event_type' => 'complaint_resolved',
                'title' => 'Complaint resolved',
                'description' => $issue->subject,
                'impact_score' => 2,
                'impact_direction' => 'positive',
                'metadata' => [
                    'issue_status' => $status,
                    'admin_id' => Auth::guard('admin')->id(),
                ],
            ]);

            return;
        }

        $this->driverScoreService->recalculateDriver($trip->driver_id, [
            'trigger' => 'support_issue_status_changed',
            'support_issue_id' => $issue->id,
            'status' => $status,
        ]);
    }
}
