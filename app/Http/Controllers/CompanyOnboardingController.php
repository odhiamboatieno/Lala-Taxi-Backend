<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyDocuments;
use App\Models\CompanyVerificationOtp;
use App\Models\Country;
use App\Models\Documents;
use App\Models\SiteSettings;
use App\Support\CompanyOnboarding;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CompanyOnboardingController extends Controller
{
    public function register(Request $request)
    {
        if (!CompanyOnboarding::enabled()) {
            return redirect('signup_company');
        }

        if (Auth::guard('company')->check()) {
            $redirect = CompanyOnboarding::dashboardRouteForStatus(Auth::guard('company')->user());
            if ($redirect) {
                return redirect($redirect);
            }

            return redirect('company/dashboard');
        }

        return $this->renderWizard($request, null);
    }

    public function show(Request $request)
    {
        if (Auth::guard('company')->guest()) {
            return redirect('signin_company');
        }

        $company = Auth::guard('company')->user();
        return $this->renderWizard($request, $company);
    }

    public function storeStep(Request $request, string $step)
    {
        abort_unless(CompanyOnboarding::isValidStep($step), 404);

        if (in_array($step, ['company-details', 'contact-person'], true) && Auth::guard('company')->guest()) {
            return $this->storePublicStep($request, $step);
        }

        abort_if(Auth::guard('company')->guest(), 403);

        $company = Auth::guard('company')->user();

        switch ($step) {
            case 'company-details':
                $validated = $this->validateCompanyDetails($request);
                $this->applyCompanyDetails($company, $validated);
                $company->save();
                break;

            case 'contact-person':
                $validated = $this->validateContactDetails($request, $company->id);
                $this->applyContactDetails($company, $validated);
                $company->save();
                break;

            case 'address':
                $validated = $this->validateAddressDetails($request);
                $this->applyAddressDetails($company, $validated);
                $company->save();
                break;

            case 'logo':
                $this->saveLogoUpload($request, $company);
                break;

            case 'documents':
                $this->saveDocumentsUpload($request, $company);
                break;

            case 'review':
                $request->validate([
                    'declaration' => 'accepted',
                ]);
                $this->finalizeSubmission($company);
                flashMessage('success', 'Company onboarding submitted successfully.');
                return redirect('company/account-under-review');
        }

        $company->onboarding_step = array_search($step, CompanyOnboarding::stepSlugs(), true) + 1;
        $company->save();

        flashMessage('success', 'Progress saved.');
        return redirect('company/onboarding?step=' . CompanyOnboarding::nextStep($step));
    }

    public function sendPhoneOtp(Request $request)
    {
        $company = Auth::guard('company')->user();
        $this->ensureOtpCanBeSent($company, 'phone');

        $otp = $this->createOtp($company, 'phone');
        $isFallback = false;

        try {
            $message = 'Your ' . SITE_NAME . ' company verification code is ' . $otp->otp;
            $smsGateway = resolve(\App\Contracts\SMSInterface::class);
            $smsGateway->send('+'.$company->country_code.$company->mobile_number, $message);
        } catch (\Exception $e) {
            if (!$this->isLocalOrDemo()) {
                throw $e;
            }

            $isFallback = true;
        }

        flashMessage('success', $isFallback ? 'OTP generated for demo/local mode: '.$otp->otp : 'Phone verification code sent.');
        return back();
    }

    public function verifyPhoneOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $company = Auth::guard('company')->user();
        $this->verifyOtpRecord($company, 'phone', $request->otp);

        $company->phone_verified_at = now();
        $company->onboarding_status = CompanyOnboarding::requireEmailVerification() ? 'email_pending' : 'documents_pending';
        $company->save();

        flashMessage('success', 'Phone number verified.');
        return redirect('company/onboarding?step=' . CompanyOnboarding::nextStep('phone-verification'));
    }

    public function sendEmailVerification(Request $request)
    {
        $company = Auth::guard('company')->user();
        $this->ensureOtpCanBeSent($company, 'email');

        $otp = $this->createOtp($company, 'email');
        $isFallback = false;

        try {
            Mail::raw(
                'Your '.SITE_NAME.' company email verification code is '.$otp->otp.'. It expires in '.config('company_onboarding.otp_expiry_minutes').' minutes.',
                function ($message) use ($company) {
                    $message->to($company->email, $company->name)
                        ->subject('Verify your company email');
                }
            );
        } catch (\Exception $e) {
            if (!$this->isLocalOrDemo()) {
                throw $e;
            }

            $isFallback = true;
        }

        flashMessage('success', $isFallback ? 'Email OTP generated for demo/local mode: '.$otp->otp : 'Verification email sent.');
        return back();
    }

    public function verifyEmailOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6',
        ]);

        $company = Auth::guard('company')->user();
        $this->verifyOtpRecord($company, 'email', $request->otp);

        $company->email_verified_at = now();
        $company->onboarding_status = 'documents_pending';
        $company->save();

        flashMessage('success', 'Email address verified.');
        return redirect('company/onboarding?step=' . CompanyOnboarding::nextStep('email-verification'));
    }

    public function uploadLogo(Request $request)
    {
        $this->saveLogoUpload($request, Auth::guard('company')->user());
        flashMessage('success', 'Company logo saved.');
        return redirect('company/onboarding?step=logo');
    }

    public function uploadDocument(Request $request)
    {
        $this->saveDocumentsUpload($request, Auth::guard('company')->user(), true);
        flashMessage('success', 'Document uploaded.');
        return redirect('company/onboarding?step=documents');
    }

    public function deleteDocument($id)
    {
        $company = Auth::guard('company')->user();
        $document = CompanyDocuments::where('company_id', $company->id)->findOrFail($id);
        $document->delete();

        flashMessage('success', 'Document removed.');
        return back();
    }

    public function accountUnderReview()
    {
        $company = Auth::guard('company')->user();
        return view('user.company_account_under_review', [
            'company' => $company,
            'status' => CompanyOnboarding::normalizedStatus($company),
            'supportEmail' => SiteSettings::where('name', 'site_email')->value('value') ?: SiteSettings::where('name', 'admin_email')->value('value'),
            'supportPhone' => SiteSettings::where('name', 'site_contact_number')->value('value'),
        ]);
    }

    protected function renderWizard(Request $request, ?Company $company)
    {
        $step = (string) $request->query('step', CompanyOnboarding::firstStep());
        if (!CompanyOnboarding::isValidStep($step)) {
            $step = CompanyOnboarding::firstStep();
        }

        $draft = session('company_onboarding_draft', []);
        if (!$company && !in_array($step, ['company-details', 'contact-person'], true)) {
            $step = count($draft) ? 'contact-person' : 'company-details';
        }

        $countries = Country::orderBy('long_name')->get();
        $defaultCountryId = old('country_id')
            ?: ($company->country_id ?? null)
            ?: ($draft['country_id'] ?? null)
            ?: CompanyOnboarding::defaultCountryId();
        $requiredDocuments = $company ? CheckDocument('Company', $company->country_id ?: 'all') : collect();
        $uploadedDocuments = $company ? collect(json_decode(UserDocuments('Company', $company, 0), true) ?: []) : collect();

        return view('user.company_onboarding_wizard', [
            'company' => $company,
            'wizardSteps' => CompanyOnboarding::steps(),
            'currentStep' => $step,
            'draft' => $draft,
            'countries' => $countries,
            'defaultCountryId' => $defaultCountryId,
            'requiredDocuments' => $requiredDocuments,
            'uploadedDocuments' => $uploadedDocuments,
            'status' => CompanyOnboarding::normalizedStatus($company),
            'phoneOtpRecord' => $company ? CompanyVerificationOtp::where('company_id', $company->id)->where('type', 'phone')->latest('id')->first() : null,
            'emailOtpRecord' => $company ? CompanyVerificationOtp::where('company_id', $company->id)->where('type', 'email')->latest('id')->first() : null,
        ]);
    }

    protected function storePublicStep(Request $request, string $step)
    {
        if ($step === 'company-details') {
            $validated = $this->validateCompanyDetails($request);
            session(['company_onboarding_draft' => array_merge(session('company_onboarding_draft', []), $validated)]);

            flashMessage('success', 'Company details saved.');
            return redirect('company/register?step=contact-person');
        }

        $draft = session('company_onboarding_draft', []);
        if (empty($draft['legal_name']) && empty($draft['name'])) {
            flashMessage('danger', 'Complete the company details step first.');
            return redirect('company/register?step=company-details');
        }

        $validated = $this->validateContactDetails($request);

        $company = new Company;
        $company->name = $draft['legal_name'] ?? $draft['name'];
        $this->applyCompanyDetails($company, $draft);
        $this->applyContactDetails($company, $validated);
        $company->country_id = $draft['country_id'] ?? CompanyOnboarding::defaultCountryId();
        $country = Country::find($company->country_id);
        $company->country_code = $validated['phone_country_code'] ?? ($country->phone_code ?? $company->country_code);
        $company->mobile_number = $validated['phone_number'];
        $company->password = $validated['password'];
        $company->status = 'Pending';
        $company->onboarding_status = CompanyOnboarding::requirePhoneVerification() ? 'phone_pending' : (CompanyOnboarding::requireEmailVerification() ? 'email_pending' : 'documents_pending');
        $company->approval_status = 'draft';
        $company->registration_reference = 'COMP-'.strtoupper(substr(md5(uniqid((string) mt_rand(), true)), 0, 10));
        $company->onboarding_step = 2;
        $company->save();

        Auth::guard('company')->login($company);
        session()->forget('company_onboarding_draft');

        flashMessage('success', 'Account created. Continue onboarding to activate your workspace.');
        return redirect('company/onboarding?step=address');
    }

    protected function validateCompanyDetails(Request $request): array
    {
        return $request->validate([
            'legal_name' => 'required|string|max:255',
            'trading_name' => 'nullable|string|max:255',
            'registration_number' => 'required|string|max:120',
            'vat_number' => 'nullable|string|max:120',
            'company_type' => 'required|string|max:120',
            'vehicles_count' => 'nullable|numeric|min:0',
            'drivers_count' => 'nullable|numeric|min:0',
            'operating_city' => 'required|string|max:190',
            'service_area' => 'nullable|string|max:190',
            'website_url' => 'nullable|url|max:255',
            'short_description' => 'nullable|string|max:1000',
        ]);
    }

    protected function validateContactDetails(Request $request, $companyId = null): array
    {
        return $request->validate([
            'contact_first_name' => 'required|string|max:120',
            'contact_last_name' => 'required|string|max:120',
            'contact_position' => 'required|string|max:120',
            'email' => 'required|email|max:255|unique:companies,email,'.(int) $companyId,
            'phone_country_code' => 'required|string|max:10',
            'phone_number' => 'required|string|min:6|max:20|unique:companies,mobile_number,'.(int) $companyId,
            'alternative_phone' => 'nullable|string|max:20',
            'password' => $companyId ? 'nullable|min:6|confirmed' : 'required|min:6|confirmed',
        ]);
    }

    protected function validateAddressDetails(Request $request): array
    {
        return $request->validate([
            'country_id' => 'required|exists:country,id',
            'state' => 'nullable|string|max:190',
            'city' => 'required|string|max:190',
            'postal_code' => 'required|string|max:64',
            'address' => 'required|string|max:255',
            'office_building_name' => 'nullable|string|max:190',
            'map_coordinates' => 'nullable|string|max:120',
        ]);
    }

    protected function applyCompanyDetails(Company $company, array $validated): void
    {
        $company->name = $validated['legal_name'] ?? $company->name;
        $company->legal_name = $validated['legal_name'] ?? $company->legal_name;
        $company->trading_name = $validated['trading_name'] ?? $company->trading_name;
        $company->registration_number = $validated['registration_number'] ?? $company->registration_number;
        $company->vat_number = $validated['vat_number'] ?? $company->vat_number;
        $company->business_type = $validated['company_type'] ?? $company->business_type;
        $company->vehicles_count = (int) ($validated['vehicles_count'] ?? 0);
        $company->drivers_count = (int) ($validated['drivers_count'] ?? 0);
        $company->city = $validated['operating_city'] ?? $company->city;
        $company->service_area = $validated['service_area'] ?? $company->service_area;
        $company->website_url = $validated['website_url'] ?? $company->website_url;
        $company->short_description = $validated['short_description'] ?? $company->short_description;
    }

    protected function applyContactDetails(Company $company, array $validated): void
    {
        $company->contact_first_name = $validated['contact_first_name'];
        $company->contact_last_name = $validated['contact_last_name'];
        $company->contact_position = $validated['contact_position'];
        $company->email = $validated['email'];
        $company->mobile_number = $validated['phone_number'];
        $company->country_code = $validated['phone_country_code'];
        $company->alternative_phone = $validated['alternative_phone'] ?? null;

        if (!empty($validated['password'])) {
            $company->password = $validated['password'];
        }
    }

    protected function applyAddressDetails(Company $company, array $validated): void
    {
        $country = Country::find($validated['country_id']);
        $company->country_id = $validated['country_id'];
        $company->country = $country->phone_code ?? $company->country;
        $company->address = $validated['address'];
        $company->city = $validated['city'];
        $company->state = $validated['state'] ?? null;
        $company->postal_code = $validated['postal_code'];
        $company->office_building_name = $validated['office_building_name'] ?? null;
        $company->map_coordinates = $validated['map_coordinates'] ?? null;
    }

    protected function saveLogoUpload(Request $request, Company $company): void
    {
        $request->validate([
            'logo' => 'required|image|mimes:jpg,jpeg,png,webp|max:'.config('company_onboarding.logo_max_size_kb'),
        ]);

        $imageUploader = resolve(\App\Contracts\ImageHandlerInterface::class);
        $target_dir = '/images/companies/'.$company->id;
        $targetPath = asset($target_dir).'/';
        $logo = $request->file('logo');
        $file_name = 'profile_'.time().'.'.$logo->getClientOriginalExtension();
        $uploadResult = $imageUploader->upload($logo, compact('target_dir', 'file_name'));

        if (!$uploadResult['status']) {
            throw ValidationException::withMessages([
                'logo' => [$uploadResult['status_message']],
            ]);
        }

        $company->profile = $targetPath.$uploadResult['file_name'];
        $company->save();
    }

    protected function saveDocumentsUpload(Request $request, Company $company, bool $single = false): void
    {
        $requiredDocuments = CheckDocument('Company', $company->country_id ?: 'all');
        $rules = [];
        $attributes = [];

        foreach ($requiredDocuments as $document) {
            $field = 'file_'.$document->id;
            if ($single && (int) $request->get('document_id') !== (int) $document->id) {
                continue;
            }

            if ($request->hasFile($field)) {
                $rules[$field] = 'mimes:jpg,jpeg,png,pdf|max:'.config('company_onboarding.document_max_size_kb');
            }

            if ($document->expiry_required) {
                $rules['expired_date_'.$document->id] = 'nullable|date|date_format:Y-m-d';
            }

            $attributes[$field] = $document->document_name;
        }

        if (count($rules)) {
            Validator::make($request->all(), $rules, [], $attributes)->validate();
        }

        $imageUploader = resolve(\App\Contracts\ImageHandlerInterface::class);
        $target_dir = '/images/companies/'.$company->id;
        $targetPath = asset($target_dir).'/';

        foreach ($requiredDocuments as $documentDefinition) {
            $field = 'file_'.$documentDefinition->id;
            if (!$request->hasFile($field)) {
                continue;
            }

            $file = $request->file($field);
            $originalName = $file->getClientOriginalName();
            $mimeType = $file->getClientMimeType() ?: $file->getMimeType();
            $fileSize = $file->getSize();
            $file_name = str_replace(' ', '_', strtolower($documentDefinition->document_name)).'_'.time().'.'.$file->getClientOriginalExtension();
            $uploadResult = $imageUploader->upload($file, compact('target_dir', 'file_name'));

            if (!$uploadResult['status']) {
                throw ValidationException::withMessages([
                    $field => [$uploadResult['status_message']],
                ]);
            }

            $companyDocument = CompanyDocuments::firstOrNew([
                'company_id' => $company->id,
                'document_id' => $documentDefinition->id,
            ]);

            $companyDocument->document = $targetPath.$uploadResult['file_name'];
            $companyDocument->status = 0;
            $companyDocument->expired_date = $request->get('expired_date_'.$documentDefinition->id);
            $companyDocument->original_name = $originalName;
            $companyDocument->mime_type = $mimeType;
            $companyDocument->file_size = $fileSize;
            $companyDocument->uploaded_at = now();
            $companyDocument->rejection_reason = null;
            $companyDocument->save();
        }
    }

    protected function finalizeSubmission(Company $company): void
    {
        $validator = Validator::make([], []);

        if (empty($company->legal_name) || empty($company->registration_number) || empty($company->business_type) || empty($company->city)) {
            $validator->errors()->add('company', 'Complete the company details step before submitting.');
        }

        if (empty($company->contact_first_name) || empty($company->contact_last_name) || empty($company->email) || empty($company->mobile_number)) {
            $validator->errors()->add('contact', 'Complete the contact person step before submitting.');
        }

        if (empty($company->address) || empty($company->postal_code) || empty($company->country_id)) {
            $validator->errors()->add('address', 'Complete the business address step before submitting.');
        }

        if (CompanyOnboarding::requirePhoneVerification() && !$company->phone_verified_at) {
            $validator->errors()->add('phone', 'Phone verification is required.');
        }

        if (CompanyOnboarding::requireEmailVerification() && !$company->email_verified_at) {
            $validator->errors()->add('email', 'Email verification is required.');
        }

        if (CompanyOnboarding::requireLogoUpload() && empty($company->profile)) {
            $validator->errors()->add('logo', 'Company logo is required.');
        }

        if (CompanyOnboarding::requireDocumentsUpload()) {
            $requiredDocumentIds = CheckDocument('Company', $company->country_id ?: 'all')->pluck('id')->toArray();
            $uploadedDocumentIds = CompanyDocuments::where('company_id', $company->id)->pluck('document_id')->toArray();
            $missing = array_diff($requiredDocumentIds, $uploadedDocumentIds);
            if (count($missing)) {
                $validator->errors()->add('documents', 'Upload all required company documents before submitting.');
            }
        }

        if ($validator->errors()->count()) {
            throw \Illuminate\Validation\ValidationException::withMessages($validator->errors()->toArray());
        }

        $company->submitted_for_approval_at = now();
        $company->onboarding_status = CompanyOnboarding::requireAdminApproval() ? 'pending_approval' : 'approved';
        $company->approval_status = CompanyOnboarding::requireAdminApproval() ? 'pending_approval' : 'approved';
        $company->status = CompanyOnboarding::requireAdminApproval() ? 'Pending' : 'Active';

        if (!CompanyOnboarding::requireAdminApproval()) {
            $company->approved_at = now();
        }

        $company->save();
    }

    protected function ensureOtpCanBeSent(Company $company, string $type): void
    {
        $latest = CompanyVerificationOtp::where('company_id', $company->id)
            ->where('type', $type)
            ->latest('id')
            ->first();

        if ($latest && $latest->created_at && now()->diffInSeconds($latest->created_at) < config('company_onboarding.otp_resend_cooldown_seconds')) {
            throw ValidationException::withMessages([
                'otp' => ['Please wait before requesting another verification code.'],
            ]);
        }
    }

    protected function createOtp(Company $company, string $type): CompanyVerificationOtp
    {
        return CompanyVerificationOtp::create([
            'company_id' => $company->id,
            'type' => $type,
            'otp' => (string) random_int(100000, 999999),
            'expires_at' => now()->addMinutes((int) config('company_onboarding.otp_expiry_minutes')),
            'attempts' => 0,
        ]);
    }

    protected function verifyOtpRecord(Company $company, string $type, string $otp): void
    {
        $record = CompanyVerificationOtp::where('company_id', $company->id)
            ->where('type', $type)
            ->whereNull('verified_at')
            ->latest('id')
            ->firstOrFail();

        if ($record->expires_at && now()->greaterThan($record->expires_at)) {
            throw ValidationException::withMessages([
                'otp' => ['The verification code has expired.'],
            ]);
        }

        if ((int) $record->attempts >= (int) config('company_onboarding.otp_max_attempts')) {
            throw ValidationException::withMessages([
                'otp' => ['Maximum verification attempts reached.'],
            ]);
        }

        $record->attempts = (int) $record->attempts + 1;
        if ((string) $record->otp !== (string) $otp) {
            $record->save();
            throw ValidationException::withMessages([
                'otp' => ['The verification code is invalid.'],
            ]);
        }

        $record->verified_at = now();
        $record->save();
    }

    protected function isLocalOrDemo(): bool
    {
        return app()->environment('local') || site_settings('demo_mode') == '1';
    }
}
