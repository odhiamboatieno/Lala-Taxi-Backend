<?php

namespace Modules\SaaSDispatch\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Currency;
use App\Models\User;
use App\Services\Corporate\CorporateAccountService;
use App\Services\Corporate\CorporateCapabilities;
use App\Services\Corporate\CorporateExposure;
use App\Services\Corporate\CorporateFinanceAuthorization;
use App\Services\Corporate\CorporateInvoiceGenerator;
use App\Services\Corporate\CorporateInvoiceLifecycle;
use App\Services\Corporate\CorporateMembershipService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
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

        return view('saasdispatch::accounts.companies', ['companies' => Company::orderBy('name')->paginate(20)]);
    }

    protected function accountsContext(CompanyContextResolver $resolver, ?Company $company): array
    {
        $context = $this->companyContext($resolver, $company);
        app(CorporateCapabilities::class)->ensureManagementEnabled($context['company']);
        $context['accountsRoute'] = $context['isAdmin'] ? 'saasdispatch.companies.accounts.' : 'saasdispatch.self.accounts.';
        $context['accountsRouteParameters'] = $context['isAdmin'] ? ['company' => $context['company']->id] : [];
        $context['canCreateAccounts'] = app(CorporateCapabilities::class)->managementEnabled($context['company']);

        return $context;
    }

    public function index(CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);

        return view('saasdispatch::accounts.index', $context + [
            'accounts' => CompanyCorporateAccount::where('company_id', $context['company']->id)->latest('id')->paginate(20, ['*'], 'accounts_page')->withQueryString(),
            'invoices' => CompanyCorporateInvoice::where('company_id', $context['company']->id)->with('account')->latest('id')->paginate(20, ['*'], 'invoices_page')->withQueryString(),
        ]);
    }

    public function createAccount(CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        abort_unless($context['canCreateAccounts'], 404);

        return view('saasdispatch::accounts.form', $context + ['account' => new CompanyCorporateAccount([
            'billing_cycle' => 'monthly', 'billing_timezone' => config('app.timezone', 'UTC'),
            'currency_code' => Currency::where('status', 'Active')->where('default_currency', '1')->value('code'),
            'payment_terms_days' => 30, 'credit_limit' => '0.00', 'status' => 'Active',
        ]), 'currencies' => Currency::where('status', 'Active')->orderBy('code')->get()]);
    }

    public function storeAccount(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $account = app(CorporateAccountService::class)->create($context['company'], $request->all(), $context['actor']);
        flashMessage('success', __('corporate.account_created'));

        return $this->accountRedirect($context, $account);
    }

    public function showAccount(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $account = $this->account($request, $context);
        $eligible = User::where('user_type', 'Rider')->where('status', 'Active')
            ->where(fn ($q) => $q->whereNull('corporate_account_id')->orWhere('corporate_account_id', $account->id));
        if (!$context['isAdmin']) {
            $eligible->where('company_id', $context['company']->id);
        }
        $term = trim((string) $request->input('rider_search', ''));
        if ($term !== '') {
            $eligible->where(fn ($q) => $q->where('first_name', 'like', '%'.$term.'%')->orWhere('last_name', 'like', '%'.$term.'%')->orWhere('email', 'like', '%'.$term.'%')->orWhere('mobile_number', 'like', '%'.$term.'%'));
        }

        return view('saasdispatch::accounts.show', $context + [
            'account' => $account, 'exposure' => app(CorporateExposure::class)->summary($account),
            'portalUsers' => \App\Models\CorporatePortalUser::where('corporate_account_id', $account->id)->orderBy('id')->paginate(20, ['*'], 'portal_page')->withQueryString(),
            'members' => $account->members()->orderBy('id')->paginate(20, ['*'], 'members_page')->withQueryString(),
            'trips' => $account->trips()->latest('id')->paginate(20, ['*'], 'trips_page')->withQueryString(),
            'invoices' => $account->invoices()->with('account')->latest('id')->paginate(20, ['*'], 'invoices_page')->withQueryString(),
            'eligibleRiders' => $eligible->orderBy('first_name')->paginate(20, ['*'], 'riders_page')->withQueryString(),
        ]);
    }

    public function editAccount(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $account = $this->account($request, $context);
        abort_if($account->archived_at, 404);

        return view('saasdispatch::accounts.form', $context + ['account' => $account, 'currencies' => Currency::where('status', 'Active')->orWhere('code', $account->currency_code)->orderBy('code')->get()]);
    }

    public function updateAccount(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $account = app(CorporateAccountService::class)->update($this->account($request, $context), $request->all(), $context['actor']);
        flashMessage('success', __('corporate.account_updated'));

        return $this->accountRedirect($context, $account);
    }

    public function archiveAccount(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $account = app(CorporateAccountService::class)->archive($this->account($request, $context), $context['actor']);
        flashMessage('success', __('corporate.account_archived'));

        return $this->accountRedirect($context, $account);
    }

    public function assignMember(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $account = $this->account($request, $context);
        $data = $request->validate(['user_id' => 'required|integer|exists:users,id', 'role' => ['required', Rule::in(['member', 'manager', 'finance'])]]);
        app(CorporateMembershipService::class)->assign($account, User::findOrFail($data['user_id']), $data['role'], $context['actor']);
        flashMessage('success', __('corporate.membership_saved'));

        return $this->accountRedirect($context, $account);
    }

    public function removeMember(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $account = $this->account($request, $context);
        app(CorporateMembershipService::class)->remove($account, User::findOrFail($request->route('rider')), $context['actor']);
        flashMessage('success', __('corporate.membership_removed'));

        return $this->accountRedirect($context, $account);
    }

    /** Compatibility URL now generates only saved trip-backed charges, never arbitrary money. */
    public function storeInvoice(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $data = $request->validate([
            'corporate_account_id' => ['required', 'integer', Rule::exists('company_corporate_accounts', 'id')->where('company_id', $context['company']->id)],
            'period_start' => 'required|date_format:Y-m-d',
        ]);
        $account = CompanyCorporateAccount::where('company_id', $context['company']->id)->findOrFail($data['corporate_account_id']);
        $invoice = app(CorporateInvoiceGenerator::class)->generate($account, $data['period_start'], false, $context['actor']);
        flashMessage($invoice ? 'success' : 'info', __($invoice ? 'corporate.invoice_created' : 'corporate.no_invoice_charges'));

        return $invoice ? $this->invoiceRedirect($context, $invoice) : $this->accountRedirect($context, $account);
    }

    public function showInvoice(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $invoice = $this->invoice($request, $context);

        return view('saasdispatch::accounts.invoice', $context + ['invoice' => $invoice->load('items', 'account'),
            'delivery' => \DB::table('company_corporate_invoice_deliveries')->where('invoice_id', $invoice->id)->first(),
            'settlement' => \DB::table('company_corporate_settlements')->where('invoice_id', $invoice->id)->first()]);
    }

    public function sendInvoice(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $invoice = app(CorporateInvoiceLifecycle::class)->send($this->invoice($request, $context), $context['actor']);
        flashMessage('success', __('corporate.invoice_issued'));

        return $this->invoiceRedirect($context, $invoice);
    }

    public function settleInvoice(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $data = $request->validate(['amount' => 'required|string', 'currency_code' => 'required|string|max:10',
            'settlement_date' => 'required|date_format:Y-m-d', 'reference' => 'required|string|max:191']);
        $invoice = app(CorporateInvoiceLifecycle::class)->settle($this->invoice($request, $context), $context['actor'],
            $data['amount'], $data['currency_code'], $data['settlement_date'], $data['reference']);
        flashMessage('success', __('corporate.invoice_paid'));

        return $this->invoiceRedirect($context, $invoice);
    }

    public function voidInvoice(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $data = $request->validate(['reason' => 'required|string|max:2000']);
        $invoice = app(CorporateInvoiceLifecycle::class)->void($this->invoice($request, $context), $context['actor'], $data['reason']);
        flashMessage('success', __('corporate.invoice_voided'));

        return $this->invoiceRedirect($context, $invoice);
    }

    public function downloadInvoice(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $invoice = $this->invoice($request, $context);
        abort_unless($invoice->trip_backed, 404);
        app(CorporateFinanceAuthorization::class)->authorize($invoice->account, $context['actor']);

        return \PDF::loadView('corporate.invoice_pdf', ['invoice' => $invoice->load('items')])->download($invoice->invoice_number.'.pdf');
    }

    public function showTrip(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $account = $this->account($request, $context);
        $trip = $account->trips()->findOrFail($request->route('trip'));

        return view('saasdispatch::accounts.trip', $context + ['account' => $account, 'trip' => $trip]);
    }

    public function savePortalUser(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $account = $this->account($request, $context);
        $staffId = $request->route('staff');
        $existing = $staffId ? \App\Models\CorporatePortalUser::where('corporate_account_id', $account->id)->findOrFail($staffId) : null;
        $record = app(\App\Services\Corporate\CorporatePortalUserService::class)->save($account, $request->all(), $context['actor'], $existing);
        if (!$record->password_set_at && $record->status === 'Active') return $this->sendPortalInvitation($context, $account, $record);
        return $this->accountRedirect($context, $account)->with('status', __('corporate.portal_access_saved'));
    }

    public function invitePortalUser(Request $request, CompanyContextResolver $resolver, ?Company $company = null)
    {
        $context = $this->accountsContext($resolver, $company);
        $account = $this->account($request, $context);
        $record = \App\Models\CorporatePortalUser::where('corporate_account_id', $account->id)->findOrFail($request->route('staff'));
        return $this->sendPortalInvitation($context, $account, $record);
    }

    private function sendPortalInvitation(array $context, CompanyCorporateAccount $account, \App\Models\CorporatePortalUser $record)
    {
        app(\App\Services\Corporate\CorporatePortalUserService::class)->authorize($account, $context['actor']);
        abort_unless((int) $record->corporate_account_id === (int) $account->id, 404);
        try { $status = app(\App\Services\Corporate\CorporatePortalUserService::class)->invite($record); }
        catch (\Throwable $error) { report($error); return $this->accountRedirect($context, $account)->withErrors(['email' => __('corporate.portal_mail_failed')]); }
        return $status === \Illuminate\Support\Facades\Password::RESET_LINK_SENT
            ? $this->accountRedirect($context, $account)->with('status', __('corporate.portal_invitation_sent'))
            : $this->accountRedirect($context, $account)->withErrors(['email' => __($status)]);
    }

    private function account(Request $request, array $context): CompanyCorporateAccount
    {
        return CompanyCorporateAccount::where('company_id', $context['company']->id)->findOrFail($request->route('account'));
    }

    private function invoice(Request $request, array $context): CompanyCorporateInvoice
    {
        return CompanyCorporateInvoice::where('company_id', $context['company']->id)->findOrFail($request->route('invoice'));
    }

    private function accountRedirect(array $context, CompanyCorporateAccount $account)
    {
        return redirect()->route($context['accountsRoute'].'accounts.show', $context['accountsRouteParameters'] + ['account' => $account->id]);
    }

    private function invoiceRedirect(array $context, CompanyCorporateInvoice $invoice)
    {
        return redirect()->route($context['accountsRoute'].'invoices.show', $context['accountsRouteParameters'] + ['invoice' => $invoice->id]);
    }
}
