<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoice;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ChecksCompanyFeatureModule;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanyAccountsController extends Controller
{
    use ChecksCompanyFeatureModule;
    use ResolvesSaaSCompanyContext;

    public function index(CompanyContextResolver $resolver)
    {
        $this->ensureCompanyFeatureEnabled('accounts_invoicing');
        $context = $this->companyContext($resolver);
        $companyId = $context['company']->id;

        return view('saasdispatch::accounts.index', array_merge($context, [
            'accounts' => CompanyCorporateAccount::query()->where('company_id', $companyId)->latest('id')->get(),
            'invoices' => CompanyCorporateInvoice::query()->where('company_id', $companyId)->with('account')->latest('id')->get(),
        ]));
    }

    public function storeAccount(Request $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $this->ensureCompanyFeatureEnabled('accounts_invoicing');
        $context = $this->companyContext($resolver);
        $request->validate([
            'account_name' => ['required', 'string', 'max:191'],
            'contact_name' => ['nullable', 'string', 'max:191'],
            'contact_email' => ['nullable', 'email', 'max:191'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'billing_cycle' => ['required', 'string', 'max:50'],
            'credit_limit' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        CompanyCorporateAccount::query()->create([
            'company_id' => $context['company']->id,
            'account_name' => $request->account_name,
            'contact_name' => $request->contact_name,
            'contact_email' => $request->contact_email,
            'contact_phone' => $request->contact_phone,
            'billing_cycle' => $request->billing_cycle,
            'credit_limit' => $request->credit_limit ?: 0,
            'status' => 'Active',
            'notes' => $request->notes,
        ]);

        flashMessage('success', 'Corporate account created successfully.');

        return redirect()->route('saasdispatch.self.accounts.index');
    }

    public function storeInvoice(Request $request, CompanyContextResolver $resolver): RedirectResponse
    {
        $this->ensureCompanyFeatureEnabled('accounts_invoicing');
        $context = $this->companyContext($resolver);
        $request->validate([
            'corporate_account_id' => ['required', 'integer'],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $subtotal = (float) $request->subtotal;
        $taxAmount = (float) ($request->tax_amount ?: 0);

        CompanyCorporateInvoice::query()->create([
            'company_id' => $context['company']->id,
            'corporate_account_id' => (int) $request->corporate_account_id,
            'invoice_number' => 'INV-'.$context['company']->id.'-'.str_pad((string) (CompanyCorporateInvoice::query()->max('id') + 1), 5, '0', STR_PAD_LEFT),
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $subtotal + $taxAmount,
            'status' => 'Draft',
            'notes' => $request->notes,
        ]);

        flashMessage('success', 'Corporate invoice created successfully.');

        return redirect()->route('saasdispatch.self.accounts.index');
    }
}
