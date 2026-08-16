<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\SaaSDispatch\Entities\SaaSSupportTicket;
use Modules\SaaSDispatch\Entities\SaaSSupportTicketMessage;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ChecksCompanyFeatureModule;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanySupportTicketController extends Controller
{
    use ChecksCompanyFeatureModule;
    use ResolvesSaaSCompanyContext;

    public function adminIndex()
    {
        return view('saasdispatch::support.admin', [
            'companies' => Company::query()->orderBy('name')->get(),
            'tickets' => SaaSSupportTicket::query()->with('company', 'messages')->latest('last_message_at')->latest('id')->get(),
        ]);
    }

    public function index(CompanyContextResolver $resolver)
    {
        $this->ensureCompanyFeatureEnabled('support_system_status');
        $context = $this->companyContext($resolver);

        return view('saasdispatch::support.index', array_merge($context, [
            'tickets' => SaaSSupportTicket::query()
                ->where('company_id', $context['company']->id)
                ->with('messages')
                ->latest('last_message_at')
                ->latest('id')
                ->get(),
        ]));
    }

    public function store(Request $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $this->ensureCompanyFeatureEnabled('support_system_status');
        $context = $this->companyContext($resolver);
        $request->validate([
            'subject' => ['required', 'string', 'max:191'],
            'category' => ['required', 'string', 'max:100'],
            'priority' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:3000'],
        ]);

        $ticket = SaaSSupportTicket::query()->create([
            'company_id' => $context['company']->id,
            'created_by_type' => 'company',
            'created_by_id' => $context['company']->id,
            'subject' => $request->subject,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open',
            'description' => $request->description,
            'last_message_at' => now(),
        ]);

        SaaSSupportTicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'company',
            'sender_id' => $context['company']->id,
            'message' => $request->description,
        ]);

        flashMessage('success', 'Support ticket created successfully.');

        return redirect()->route('saasdispatch.self.support.index');
    }

    public function adminStore(Request $request): RedirectResponse
    {
        $request->validate([
            'company_id' => ['required', 'integer'],
            'subject' => ['required', 'string', 'max:191'],
            'category' => ['required', 'string', 'max:100'],
            'priority' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:3000'],
        ]);

        $ticket = SaaSSupportTicket::query()->create([
            'company_id' => (int) $request->company_id,
            'created_by_type' => 'admin',
            'created_by_id' => auth('admin')->id(),
            'subject' => $request->subject,
            'category' => $request->category,
            'priority' => $request->priority,
            'status' => 'open',
            'description' => $request->description,
            'assigned_admin_id' => auth('admin')->id(),
            'last_message_at' => now(),
        ]);

        SaaSSupportTicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'sender_id' => auth('admin')->id(),
            'message' => $request->description,
        ]);

        flashMessage('success', 'Company support ticket created successfully.');

        return redirect()->route('saasdispatch.admin.support.index');
    }

    public function reply(Request $request, CompanyContextResolver $resolver, SaaSSupportTicket $ticket): RedirectResponse
    {
        $this->ensureCompanyFeatureEnabled('support_system_status');
        $context = $this->companyContext($resolver);
        abort_unless((int) $ticket->company_id === (int) $context['company']->id, 404);

        $request->validate([
            'message' => ['required', 'string', 'max:3000'],
            'status' => ['nullable', 'in:open,pending,resolved,closed'],
        ]);

        SaaSSupportTicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'company',
            'sender_id' => $context['company']->id,
            'message' => $request->message,
        ]);

        $ticket->update([
            'status' => $request->status ?: $ticket->status,
            'last_message_at' => now(),
            'resolved_at' => $request->status === 'resolved' ? now() : $ticket->resolved_at,
        ]);

        flashMessage('success', 'Ticket updated successfully.');

        return redirect()->route('saasdispatch.self.support.index');
    }

    public function adminReply(Request $request, SaaSSupportTicket $ticket): RedirectResponse
    {
        $request->validate([
            'message' => ['required', 'string', 'max:3000'],
            'status' => ['nullable', 'in:open,pending,resolved,closed'],
        ]);

        SaaSSupportTicketMessage::query()->create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'sender_id' => auth('admin')->id(),
            'message' => $request->message,
        ]);

        $ticket->update([
            'status' => $request->status ?: $ticket->status,
            'assigned_admin_id' => auth('admin')->id(),
            'last_message_at' => now(),
            'resolved_at' => $request->status === 'resolved' ? now() : $ticket->resolved_at,
        ]);

        flashMessage('success', 'Company support ticket updated successfully.');

        return redirect()->route('saasdispatch.admin.support.index');
    }
}
