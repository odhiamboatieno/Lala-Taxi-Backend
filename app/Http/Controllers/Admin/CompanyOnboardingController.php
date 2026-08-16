<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyDocuments;
use App\Models\User;
use App\Support\CompanyOnboarding;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class CompanyOnboardingController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'pending_approval');
        $search = trim((string) $request->get('search'));

        $query = Company::query()->withCount(['drivers as drivers_count']);

        if ($search !== '') {
            $query->where(function ($inner) use ($search) {
                $inner->where('name', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhere('registration_reference', 'like', '%'.$search.'%');
            });
        }

        if ($status !== '') {
            $query->where(function ($inner) use ($status) {
                $inner->where('onboarding_status', $status)
                    ->orWhere('approval_status', $status);
            });
        }

        return view('admin.company_onboarding.index', [
            'companies' => $query->latest('submitted_for_approval_at')->latest('id')->paginate(20)->withQueryString(),
            'statusFilter' => $status,
            'search' => $search,
            'requireAdminApproval' => CompanyOnboarding::requireAdminApproval(),
        ]);
    }

    public function show(Company $company)
    {
        $requiredDocuments = CheckDocument('Company', $company->country_id ?: 'all');
        $uploadedDocuments = CompanyDocuments::where('company_id', $company->id)->get();

        return view('admin.company_onboarding.show', [
            'company' => $company,
            'requiredDocuments' => $requiredDocuments,
            'uploadedDocuments' => $uploadedDocuments,
            'driversCount' => User::where('company_id', $company->id)->where('user_type', 'Driver')->count(),
        ]);
    }

    public function approve(Request $request, Company $company)
    {
        $company->onboarding_status = 'approved';
        $company->approval_status = 'approved';
        $company->status = 'Active';
        $company->approved_at = now();
        $company->approved_by = Auth::guard('admin')->id();
        $company->rejected_at = null;
        $company->rejected_by = null;
        $company->rejection_reason = null;
        $company->admin_notes = $request->get('admin_notes', $company->admin_notes);
        $company->save();

        $this->sendStatusEmail($company, 'Company onboarding approved', 'Your company onboarding has been approved. You can now access the dashboard.');

        flashMessage('success', 'Company approved successfully.');
        return back();
    }

    public function reject(Request $request, Company $company)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $company->onboarding_status = 'rejected';
        $company->approval_status = 'rejected';
        $company->status = 'Pending';
        $company->rejected_at = now();
        $company->rejected_by = Auth::guard('admin')->id();
        $company->rejection_reason = $request->rejection_reason;
        $company->admin_notes = $request->get('admin_notes', $company->admin_notes);
        $company->save();

        $this->sendStatusEmail($company, 'Company onboarding update', 'Your onboarding submission needs changes: '.$request->rejection_reason);

        flashMessage('success', 'Company rejected and notified.');
        return back();
    }

    public function requestResubmission(Request $request, Company $company)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $company->onboarding_status = 'documents_pending';
        $company->approval_status = 'rejected';
        $company->status = 'Pending';
        $company->rejection_reason = $request->rejection_reason;
        $company->admin_notes = $request->get('admin_notes', $company->admin_notes);
        $company->save();

        CompanyDocuments::where('company_id', $company->id)
            ->whereIn('status', [0, 2])
            ->update([
                'status' => 0,
                'reviewed_at' => now(),
                'reviewed_by' => Auth::guard('admin')->id(),
            ]);

        $this->sendStatusEmail($company, 'Company documents require resubmission', 'Please update and resubmit your company documents. Reason: '.$request->rejection_reason);

        flashMessage('success', 'Resubmission requested.');
        return back();
    }

    public function suspend(Request $request, Company $company)
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $company->onboarding_status = 'suspended';
        $company->approval_status = 'suspended';
        $company->status = 'Pending';
        $company->rejection_reason = $request->rejection_reason;
        $company->admin_notes = $request->get('admin_notes', $company->admin_notes);
        $company->save();

        $this->sendStatusEmail($company, 'Company account suspended', 'Your company account is suspended. Reason: '.$request->rejection_reason);

        flashMessage('success', 'Company suspended.');
        return back();
    }

    protected function sendStatusEmail(Company $company, string $subject, string $body): void
    {
        try {
            Mail::raw($body, function ($message) use ($company, $subject) {
                $message->to($company->email, $company->name)->subject($subject);
            });
        } catch (\Exception $e) {
        }
    }
}
