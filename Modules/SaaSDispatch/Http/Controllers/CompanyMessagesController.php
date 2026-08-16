<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\SaaSDispatch\Entities\CompanyMessage;
use Modules\SaaSDispatch\Entities\CompanyMessageThread;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ChecksCompanyFeatureModule;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanyMessagesController extends Controller
{
    use ChecksCompanyFeatureModule;
    use ResolvesSaaSCompanyContext;

    public function index(CompanyContextResolver $resolver)
    {
        $this->ensureCompanyFeatureEnabled('messages');
        $context = $this->companyContext($resolver);
        $companyId = $context['company']->id;

        return view('saasdispatch::messages.index', array_merge($context, [
            'threads' => CompanyMessageThread::query()->where('company_id', $companyId)->with('messages')->latest('last_message_at')->latest('id')->get(),
        ]));
    }

    public function storeThread(Request $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $this->ensureCompanyFeatureEnabled('messages');
        $context = $this->companyContext($resolver);
        $request->validate([
            'subject' => ['required', 'string', 'max:191'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        $thread = CompanyMessageThread::query()->create([
            'company_id' => $context['company']->id,
            'subject' => $request->subject,
            'participant_type' => 'staff',
            'status' => 'open',
            'last_message_at' => now(),
            'green_flash' => true,
        ]);

        CompanyMessage::query()->create([
            'thread_id' => $thread->id,
            'sender_type' => 'company',
            'sender_id' => $context['company']->id,
            'message' => $request->message,
        ]);

        flashMessage('success', 'Message thread created successfully.');

        return redirect()->route('saasdispatch.self.messages.index');
    }

    public function reply(Request $request, CompanyContextResolver $resolver, CompanyMessageThread $thread): RedirectResponse
    {
        $this->ensureCompanyFeatureEnabled('messages');
        $context = $this->companyContext($resolver);
        abort_unless((int) $thread->company_id === (int) $context['company']->id, 404);

        $request->validate([
            'message' => ['required', 'string', 'max:3000'],
        ]);

        CompanyMessage::query()->create([
            'thread_id' => $thread->id,
            'sender_type' => 'company',
            'sender_id' => $context['company']->id,
            'message' => $request->message,
        ]);

        $thread->update([
            'last_message_at' => now(),
            'green_flash' => true,
        ]);

        flashMessage('success', 'Reply sent successfully.');

        return redirect()->route('saasdispatch.self.messages.index');
    }
}
