<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\Corporate\CorporateCapabilities;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;
use Modules\SaaSDispatch\Entities\CompanyCorporateAccount;
use Modules\SaaSDispatch\Entities\CompanyCorporateInvoice;
use Modules\SaaSDispatch\Http\Controllers\Concerns\ResolvesSaaSCompanyContext;
use Modules\SaaSDispatch\Services\CompanyContextResolver;

class CompanyAccountsController extends Controller
{
    use ResolvesSaaSCompanyContext;

    public function companies(CompanyContextResolver $resolver)
    {
        abort_unless($resolver->isAdmin(), 403);

        return view('saasdispatch::accounts.companies', ['companies' => Company::query()->orderBy('name')->paginate(20)]);
    }

    protected function accountsContext(CompanyContextResolver $resolver, ?Company $company): array
    {
        $context = $this->companyContext($resolver, $company);
        app(CorporateCapabilities::class)->ensureManagementEnabled($context['company']);
        $context['accountsRoute'] = $context['isAdmin'] ? 'saasdispatch.companies.accounts.' : 'saasdispatch.self.accounts.';
        $context['accountsRouteParameters'] = $context['isAdmin'] ? ['company' => $context['company']->id] : [];

        return $context;
    }

    public function index(CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $companyId = $context['company']->id;

        return view('saasdispatch::accounts.index', array_merge($context, [
            'accounts' => CompanyCorporateAccount::query()->where('company_id', $companyId)->latest('id')->get(),
            'invoices' => CompanyCorporateInvoice::query()->where('company_id', $companyId)->with('account')->latest('id')->get(),
        ]));
    }

    public function storeAccount(Request $request, CompanyContextResolver $resolver, ?Company $company = null): RedirectResponse
    {
        $context = $this->accountsContext($resolver, $company);
        $request->validate([
            'account_name' => ['required', 'string', 'max:191'],
            'contact_name' => ['nullable', 'string', 'max:191'],
            'contact_email' => ['nullable', 'email', 'max:191'],
            'contact_phone' => ['nullable', 'string', 'max:50'],
            'billing_cycle' => ['required', Rule::in(['weekly', 'monthly', 'quarterly'])],
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

        flashMessage('success', __('corporate.account_created'));

        return redirect()->route($context['accountsRoute'].'index', $context['accountsRouteParameters']);
    }

    public function storeInvoice(Request $request, CompanyContextResolver $resolver, ?Company $company = null): RedirectResponse
    {
        $context = $this->accountsContext($resolver, $company);
        $request->validate([
            'corporate_account_id' => ['required', 'integer', Rule::exists('company_corporate_accounts', 'id')->where('company_id', $context['company']->id)],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'subtotal' => ['required', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $subtotal = (float) $request->subtotal;
        $taxAmount = (float) ($request->tax_amount ?: 0);

        CompanyCorporateInvoice::query()->create([
            'company_id' => $context['company']->id,
            'corporate_account_id' => (int) $request->corporate_account_id,
            'invoice_number' => 'INV-'.$context['company']->id.'-'.Str::ulid(),
            'invoice_date' => $request->invoice_date,
            'due_date' => $request->due_date,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $subtotal + $taxAmount,
            'status' => 'Draft',
            'notes' => $request->notes,
        ]);

        flashMessage('success', __('corporate.invoice_created'));

        return redirect()->route($context['accountsRoute'].'index', $context['accountsRouteParameters']);
    }
}
