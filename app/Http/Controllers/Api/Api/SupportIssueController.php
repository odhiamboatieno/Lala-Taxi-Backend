<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportIssue;
use App\Models\SupportIssueMessage;
use App\Models\Trips;
use App\Services\DriverScore\DriverScoreService;
use Illuminate\Http\Request;
use JWTAuth;
use Validator;

class SupportIssueController extends Controller
{
    public function __construct(protected DriverScoreService $driverScoreService)
    {
    }

    public function index()
    {
        $user = JWTAuth::parseToken()->authenticate();

        $issues = SupportIssue::with(['messages' => function ($query) {
                $query->latest()->limit(1);
            }])
            ->where('user_id', $user->id)
            ->latest('last_message_at')
            ->latest('id')
            ->limit(30)
            ->get()
            ->map(function ($issue) {
                return $this->formatIssue($issue, false);
            });

        return response()->json([
            'status_code' => '1',
            'status_message' => trans('messages.api.success'),
            'issues' => $issues,
        ]);
    }

    public function store(Request $request)
    {
        $user = JWTAuth::parseToken()->authenticate();
        $senderType = $this->senderTypeForUser($user);

        $validator = Validator::make($request->all(), [
            'type' => 'required|in:chat,issue',
            'category' => 'required|string|max:80',
            'subject' => 'required|string|max:160',
            'description' => 'nullable|string|max:3000',
            'priority' => 'nullable|in:low,normal,high,urgent',
            'trip_id' => 'nullable|exists:trips,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

        if ($request->trip_id) {
            $tripLookup = Trips::where('id', $request->trip_id);
            if ($senderType === 'driver') {
                $tripLookup->where('driver_id', $user->id);
            } else {
                $tripLookup->where('user_id', $user->id);
            }
            $tripExistsForUser = $tripLookup->exists();

            if (!$tripExistsForUser) {
                return response()->json([
                    'status_code' => '0',
                    'status_message' => 'Trip not found for this account.',
                ]);
            }
        }

        $issue = SupportIssue::create([
            'user_id' => $user->id,
            'trip_id' => $request->trip_id,
            'type' => $request->type,
            'category' => $request->category,
            'subject' => $request->subject,
            'description' => $request->description,
            'priority' => $request->priority ?: 'normal',
            'status' => 'open',
            'last_message_at' => now(),
        ]);

        $message = trim((string) ($request->description ?: $request->subject));
        if ($message !== '') {
            SupportIssueMessage::create([
                'support_issue_id' => $issue->id,
                'sender_id' => $user->id,
                'sender_type' => 'rider',
                'message' => $message,
            ]);
        }

        if ($senderType === 'rider' && $request->type === 'issue' && $issue->trip_id) {
            $trip = Trips::find($issue->trip_id);
            if ($trip && $trip->driver_id) {
                $this->driverScoreService->logEvent($trip->driver_id, [
                    'event_key' => 'support_issue_'.$issue->id,
                    'trip_id' => $trip->id,
                    'support_issue_id' => $issue->id,
                    'source_type' => 'support_issue',
                    'event_type' => 'complaint_opened',
                    'title' => 'Rider complaint opened',
                    'description' => $issue->subject,
                    'impact_score' => -4,
                    'impact_direction' => 'negative',
                    'metadata' => [
                        'category' => $issue->category,
                        'priority' => $issue->priority,
                    ],
                ]);
            }
        }

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Support request created.',
            'issue' => $this->formatIssue($issue->fresh(['messages']), true),
        ]);
    }

    public function show($issue)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $supportIssue = SupportIssue::with('messages')
            ->where('user_id', $user->id)
            ->findOrFail($issue);

        return response()->json([
            'status_code' => '1',
            'status_message' => trans('messages.api.success'),
            'issue' => $this->formatIssue($supportIssue, true),
        ]);
    }

    public function message(Request $request, $issue)
    {
        $user = JWTAuth::parseToken()->authenticate();

        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:3000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status_code' => '0',
                'status_message' => $validator->messages()->first(),
            ]);
        }

        $supportIssue = SupportIssue::where('user_id', $user->id)->findOrFail($issue);

        SupportIssueMessage::create([
            'support_issue_id' => $supportIssue->id,
            'sender_id' => $user->id,
            'sender_type' => 'rider',
            'message' => $request->message,
        ]);

        $supportIssue->status = $supportIssue->status === 'resolved'
            ? 'open'
            : $supportIssue->status;
        $supportIssue->last_message_at = now();
        $supportIssue->save();

        return response()->json([
            'status_code' => '1',
            'status_message' => 'Message sent.',
            'issue' => $this->formatIssue($supportIssue->fresh(['messages']), true),
        ]);
    }

    protected function formatIssue(SupportIssue $issue, $withMessages)
    {
        $data = [
            'id' => $issue->id,
            'trip_id' => $issue->trip_id,
            'type' => $issue->type,
            'category' => $issue->category,
            'subject' => $issue->subject,
            'description' => $issue->description,
            'priority' => $issue->priority,
            'status' => $issue->status,
            'last_message_at' => optional($issue->last_message_at)->toIso8601String(),
            'created_at' => optional($issue->created_at)->toIso8601String(),
        ];

        if ($withMessages) {
            $data['messages'] = $issue->messages
                ->sortBy('created_at')
                ->values()
                ->map(function ($message) {
                    return [
                        'id' => $message->id,
                        'sender_type' => $message->sender_type,
                        'message' => $message->message,
                        'created_at' => optional($message->created_at)->toIso8601String(),
                    ];
                });
        } else {
            $latest = $issue->messages->first();
            $data['latest_message'] = $latest ? $latest->message : '';
        }

        return $data;
    }

    protected function senderTypeForUser($user)
    {
        return strtolower((string) $user->user_type) === 'driver' ? 'driver' : 'rider';
    }
}
