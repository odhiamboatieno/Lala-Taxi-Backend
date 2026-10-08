<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use App\Models\CorporatePortalUser;
use App\Models\User;
use App\Services\Corporate\CorporateExposure;
use App\Services\Corporate\CorporateMembershipService;

use App\Services\Corporate\CorporatePortalUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class PortalController extends Controller
{
    private function context(Request $request): array
    {
        $actor = $request->attributes->get('corporate_portal_user');
        abort_unless($actor instanceof CorporatePortalUser, 401);
        return ['actor' => $actor, 'account' => $actor->account];
    }

    public function dashboard(Request $request)
    {
        $context = $this->context($request);
        $account = $context['account'];
        return view('corporate.portal.dashboard', $context + [
            'membersCount' => $account->members()->where('status', 'Active')->count(),
            'tripsCount' => $account->trips()->where('billing_mode', 'corporate')->whereNotNull('billing_finalized_at')->count(),
            'exposure' => app(CorporateExposure::class)->summary($account),
            'trips' => $account->trips()->where('billing_mode', 'corporate')->with('users')->latest('id')->limit(5)->get(),
        ]);
    }

    public function members(Request $request)
    {
        $context = $this->context($request);
        abort_unless($context['actor']->canManageUsers(), 403);
        return view('corporate.portal.members', $context + [
            'members' => $context['account']->members()->orderBy('first_name')->paginate(20)->withQueryString(),
        ]);
    }

    public function assignMember(Request $request)
    {
        $context = $this->context($request);
        abort_unless($context['actor']->canManageUsers(), 403);
        $data = $request->validate(['country_code' => 'required|regex:/^[0-9]{1,4}$/',
            'mobile_number' => 'required|regex:/^[0-9]{5,20}$/', 'role' => 'required|in:member,manager,finance']);
        $rider = User::where('user_type', 'Rider')->where('status', 'Active')->where('company_id', $context['account']->company_id)
            ->where('country_code', $data['country_code'])->where('mobile_number', $data['mobile_number'])
            ->where(fn ($query) => $query->whereNull('corporate_account_id')->orWhere('corporate_account_id', $context['account']->id))->first();
        if (!$rider) throw ValidationException::withMessages(['mobile_number' => __('corporate.portal_eligible_rider')]);
        app(CorporateMembershipService::class)->assign($context['account'], $rider, $data['role'], $context['actor']);
        return redirect()->route('corporate.portal.members')->with('status', __('corporate.membership_saved'));
    }

    public function removeMember(Request $request, string $rider)
    {
        $context = $this->context($request);
        abort_unless($context['actor']->canManageUsers(), 403);
        abort_unless($context['account']->status === 'Active' && !$context['account']->archived_at, 409);
        $member = $context['account']->members()->findOrFail($rider);
        app(CorporateMembershipService::class)->remove($context['account'], $member, $context['actor']);
        return redirect()->route('corporate.portal.members')->with('status', __('corporate.membership_removed'));
    }

    public function staff(Request $request)
    {
        $context = $this->context($request);
        abort_unless($context['actor']->canManageUsers(), 403);
        return view('corporate.portal.staff', $context + ['staff' => CorporatePortalUser::where('corporate_account_id', $context['account']->id)->orderBy('id')->paginate(20)]);
    }

    public function saveStaff(Request $request, ?string $staff = null)
    {
        $context = $this->context($request);
        abort_unless($context['actor']->canManageUsers(), 403);
        $existing = $staff ? CorporatePortalUser::where('corporate_account_id', $context['account']->id)->findOrFail($staff) : null;
        $service = app(CorporatePortalUserService::class);
        $record = $service->save($context['account'], $request->all(), $context['actor'], $existing);
        if (!$record->password_set_at && $record->status === 'Active') return $this->sendInvitation($record, 'corporate.portal.staff');
        return redirect()->route('corporate.portal.staff')->with('status', __('corporate.portal_access_saved'));
    }

    public function inviteStaff(Request $request, string $staff)
    {
        $context = $this->context($request);
        abort_unless($context['actor']->canManageUsers(), 403);
        $record = CorporatePortalUser::where('corporate_account_id', $context['account']->id)->findOrFail($staff);
        return $this->sendInvitation($record, 'corporate.portal.staff');
    }

    private function sendInvitation(CorporatePortalUser $user, string $route)
    {
        try { $status = app(CorporatePortalUserService::class)->invite($user); }
        catch (\Throwable $error) { report($error); return redirect()->route($route)->withErrors(['email' => __('corporate.portal_mail_failed')]); }
        return $status === Password::RESET_LINK_SENT
            ? redirect()->route($route)->with('status', __('corporate.portal_invitation_sent'))
            : redirect()->route($route)->withErrors(['email' => __($status)]);
    }

    public function trips(Request $request)
    {
        $context = $this->context($request);
        return view('corporate.portal.trips', $context + ['trips' => $context['account']->trips()->where('billing_mode', 'corporate')->with('users')->latest('id')->paginate(20)->withQueryString()]);
    }

    public function trip(Request $request, string $trip)
    {
        $context = $this->context($request);
        return view('corporate.portal.trip', $context + ['trip' => $context['account']->trips()->where('billing_mode', 'corporate')->with('users')->findOrFail($trip)]);
    }

    public function invoices(Request $request)
    {
        $context = $this->context($request);
        abort_unless($context['actor']->canViewInvoices(), 403);
        return view('corporate.portal.invoices', $context + ['invoices' => $this->publishedInvoices($context)->latest('id')->paginate(20)]);
    }

    public function invoice(Request $request, string $invoice)
    {
        $context = $this->context($request);
        abort_unless($context['actor']->canViewInvoices(), 403);
        return view('corporate.portal.invoice', $context + ['invoice' => $this->publishedInvoices($context)->with('items')->findOrFail($invoice)]);
    }

    public function download(Request $request, string $invoice)
    {
        $context = $this->context($request);
        abort_unless($context['actor']->canViewInvoices(), 403);
        $record = $this->publishedInvoices($context)->with('items')->findOrFail($invoice);
        return \PDF::loadView('corporate.invoice_pdf', ['invoice' => $record])->download($record->invoice_number.'.pdf');
    }

    private function publishedInvoices(array $context)
    {
        return $context['account']->invoices()->where('trip_backed', true)->whereIn('status', ['Sent', 'Paid', 'Void']);
    }
}
