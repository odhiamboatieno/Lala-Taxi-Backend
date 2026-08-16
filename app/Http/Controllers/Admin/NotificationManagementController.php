<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NotificationTemplate;
use App\Services\Notifications\NotificationManager;
use Illuminate\Http\Request;

class NotificationManagementController extends Controller
{
    protected $notifications;

    public function __construct(NotificationManager $notifications)
    {
        $this->notifications = $notifications;
    }

    public function index()
    {
        $companyId = $this->scopeCompanyId();

        return view('admin.notifications.index', [
            'activeCategory' => request('category', 'scheduled_trip'),
            'templatesByCategory' => $this->notifications->groupedTemplates($companyId),
            'placeholdersByCategory' => $this->notifications->placeholdersByCategory($companyId),
        ]);
    }

    public function update(Request $request)
    {
        $companyId = $this->scopeCompanyId();
        $templates = $request->get('templates', []);

        foreach ($templates as $eventKey => $values) {
            $template = NotificationTemplate::query()
                ->where('company_id', $companyId)
                ->where('event_key', $eventKey)
                ->first();

            if (! $template) {
                continue;
            }

            $template->push_title = $values['push_title'] ?? $template->push_title;
            $template->push_message = $values['push_message'] ?? $template->push_message;
            $template->sms_message = $values['sms_message'] ?? $template->sms_message;
            $template->push_enabled = ! empty($values['push_enabled']);
            $template->sms_enabled = ! empty($values['sms_enabled']);
            $template->is_active = ! empty($values['is_active']);
            $template->save();
        }

        flashMessage('success', 'Notification templates updated successfully');

        return redirect()->back();
    }

    protected function scopeCompanyId(): int
    {
        return LOGIN_USER_TYPE === 'company' ? (int) auth()->guard('company')->id() : 0;
    }
}
