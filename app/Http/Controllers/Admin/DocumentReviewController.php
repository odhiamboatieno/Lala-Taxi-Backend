<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\CompanyDocuments;
use App\Models\DriverDocuments;
use App\Models\ProfilePicture;
use App\Models\User;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Auth;

class DocumentReviewController extends Controller
{
    public function driverDocuments(Request $request)
    {
        $query = DriverDocuments::query()
            ->where('type', 'Driver')
            ->where('vehicle_id', 0);

        if (LOGIN_USER_TYPE == 'company') {
            $query->whereIn('user_id', User::where('company_id', Auth::guard('company')->id())->pluck('id'));
        }

        $this->applyStatusFilter($query, $request->get('status'));
        $this->applySearchFilter($query, $request->get('q'), 'driver');

        $records = $query->orderByRaw("CASE WHEN status = '0' THEN 0 WHEN status = '2' THEN 1 ELSE 2 END")
            ->latest('id')
            ->paginate(20)
            ->appends($request->query());

        $users = User::withTrashed()->with('company')->whereIn('id', $records->pluck('user_id')->all())->get()->keyBy('id');

        return view('admin.document_reviews.index', [
            'scope' => 'driver',
            'title' => 'Driver Documents Review',
            'subtitle' => 'Approve or reject driver compliance documents without editing the whole profile.',
            'records' => $records,
            'users' => $users,
            'vehicles' => collect(),
            'companies' => collect(),
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function vehicleDocuments(Request $request)
    {
        $query = DriverDocuments::query()
            ->where('type', 'Vehicle')
            ->where('vehicle_id', '!=', 0);

        if (LOGIN_USER_TYPE == 'company') {
            $query->whereIn('user_id', User::where('company_id', Auth::guard('company')->id())->pluck('id'));
        }

        $this->applyStatusFilter($query, $request->get('status'));
        $this->applySearchFilter($query, $request->get('q'), 'vehicle');

        $records = $query->orderByRaw("CASE WHEN status = '0' THEN 0 WHEN status = '2' THEN 1 ELSE 2 END")
            ->latest('id')
            ->paginate(20)
            ->appends($request->query());

        $users = User::withTrashed()->with('company')->whereIn('id', $records->pluck('user_id')->all())->get()->keyBy('id');
        $vehicles = Vehicle::whereIn('id', $records->pluck('vehicle_id')->all())->get()->keyBy('id');

        return view('admin.document_reviews.index', [
            'scope' => 'vehicle',
            'title' => 'Vehicle Documents Review',
            'subtitle' => 'Review plate, insurance, permit, and other fleet compliance documents in one queue.',
            'records' => $records,
            'users' => $users,
            'vehicles' => $vehicles,
            'companies' => collect(),
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function companyDocuments(Request $request)
    {
        $query = CompanyDocuments::query();

        if (LOGIN_USER_TYPE == 'company') {
            $query->where('company_id', Auth::guard('company')->id());
        }

        $this->applyStatusFilter($query, $request->get('status'));
        $this->applySearchFilter($query, $request->get('q'), 'company');

        $records = $query->orderByRaw("CASE WHEN status = '0' THEN 0 WHEN status = '2' THEN 1 ELSE 2 END")
            ->latest('id')
            ->paginate(20)
            ->appends($request->query());

        $companies = Company::whereIn('id', $records->pluck('company_id')->all())->get()->keyBy('id');

        return view('admin.document_reviews.index', [
            'scope' => 'company',
            'title' => 'Company Documents Review',
            'subtitle' => 'Approve operator/company compliance documents from a dedicated review queue.',
            'records' => $records,
            'users' => collect(),
            'vehicles' => collect(),
            'companies' => $companies,
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function profileImages(Request $request)
    {
        $query = ProfilePicture::query()
            ->whereNotNull('pending_src')
            ->where('pending_src', '!=', '');

        if (LOGIN_USER_TYPE == 'company') {
            $query->whereIn('user_id', User::where('company_id', Auth::guard('company')->id())->pluck('id'));
        }

        $status = trim((string) $request->get('status'));
        if ($status !== '') {
            $query->where('approval_status', $status);
        }

        $search = trim((string) $request->get('q'));
        if ($search !== '') {
            $query->whereIn('user_id', User::query()
                ->where('user_type', 'Driver')
                ->where(function ($builder) use ($search) {
                    $builder->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                })->pluck('id'));
        }

        $records = $query->latest('submitted_at')->paginate(20)->appends($request->query());
        $users = User::withTrashed()->with('company')->whereIn('id', $records->pluck('user_id')->all())->get()->keyBy('id');

        return view('admin.document_reviews.index', [
            'scope' => 'profile_image',
            'title' => 'Driver Profile Image Review',
            'subtitle' => 'Approve pending driver profile image changes before they go live in the app.',
            'records' => $records,
            'users' => $users,
            'vehicles' => collect(),
            'companies' => collect(),
            'filters' => $request->only(['status', 'q']),
        ]);
    }

    public function updateDriverDocument(Request $request, DriverDocuments $document)
    {
        $this->authorizeDriverDocument($document);

        $data = $request->validate([
            'status' => ['required', Rule::in(['1', '2'])],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['status'] === '2' && trim((string) ($data['rejection_reason'] ?? '')) === '') {
            return back()->withErrors(['rejection_reason' => 'A rejection reason is required.'])->withInput();
        }

        $document->status = $data['status'];
        $document->rejection_reason = $data['status'] === '2' ? $data['rejection_reason'] : null;
        $document->reviewed_at = now();
        $document->reviewed_by = $this->reviewerId();
        $document->save();

        if ($document->type === 'Vehicle') {
            $this->syncVehicleAfterReview($document->vehicle_id);
        } else {
            $this->syncDriverAfterReview($document->user_id);
        }

        flashMessage('success', 'Document review updated successfully.');
        return back();
    }

    public function updateCompanyDocument(Request $request, CompanyDocuments $document)
    {
        if (LOGIN_USER_TYPE == 'company') {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(['1', '2'])],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['status'] === '2' && trim((string) ($data['rejection_reason'] ?? '')) === '') {
            return back()->withErrors(['rejection_reason' => 'A rejection reason is required.'])->withInput();
        }

        $document->status = $data['status'];
        $document->rejection_reason = $data['status'] === '2' ? $data['rejection_reason'] : null;
        $document->reviewed_at = now();
        $document->reviewed_by = $this->reviewerId();
        $document->save();

        flashMessage('success', 'Company document review updated successfully.');
        return back();
    }

    public function updateProfileImage(Request $request, int $userId)
    {
        $profile = ProfilePicture::firstOrNew(['user_id' => $userId]);
        $user = User::withTrashed()->findOrFail($userId);

        if (LOGIN_USER_TYPE == 'company' && (int) $user->company_id !== (int) Auth::guard('company')->id()) {
            abort(403);
        }

        $data = $request->validate([
            'status' => ['required', Rule::in(['approved', 'rejected'])],
            'rejection_reason' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($data['status'] === 'rejected' && trim((string) ($data['rejection_reason'] ?? '')) === '') {
            return back()->withErrors(['rejection_reason' => 'A rejection reason is required.'])->withInput();
        }

        if ($data['status'] === 'approved') {
            $profile->src = $profile->pending_src ?: $profile->src;
            $profile->pending_src = null;
            $profile->approval_status = 'approved';
            $profile->rejection_reason = null;
        } else {
            $profile->approval_status = 'rejected';
            $profile->rejection_reason = $data['rejection_reason'];
        }

        $profile->reviewed_at = now();
        $profile->reviewed_by = $this->reviewerId();
        $profile->save();

        flashMessage('success', 'Profile image review updated successfully.');
        return back();
    }

    protected function applyStatusFilter($query, ?string $status): void
    {
        $status = trim((string) $status);
        if ($status !== '') {
            $query->where('status', $status);
        }
    }

    protected function applySearchFilter($query, ?string $search, string $scope): void
    {
        $search = trim((string) $search);
        if ($search === '') {
            return;
        }

        if ($scope === 'company') {
            $query->where(function ($builder) use ($search) {
                $builder->whereIn('company_id', Company::query()
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile_number', 'like', "%{$search}%")
                    ->pluck('id'))
                    ->orWhereIn('document_id', \App\Models\Documents::query()->where('document_name', 'like', "%{$search}%")->pluck('id'));
            });
            return;
        }

        if ($scope === 'vehicle') {
            $query->where(function ($builder) use ($search) {
                $builder->whereIn('user_id', User::query()
                    ->where('user_type', 'Driver')
                    ->where(function ($inner) use ($search) {
                        $inner->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('mobile_number', 'like', "%{$search}%");
                    })->pluck('id'))
                    ->orWhereIn('vehicle_id', Vehicle::query()
                        ->where('vehicle_number', 'like', "%{$search}%")
                        ->orWhere('vehicle_name', 'like', "%{$search}%")
                        ->pluck('id'))
                    ->orWhereIn('document_id', \App\Models\Documents::query()->where('document_name', 'like', "%{$search}%")->pluck('id'));
            });
            return;
        }

        $query->where(function ($builder) use ($search) {
            $builder->whereIn('user_id', User::query()
                ->where('user_type', 'Driver')
                ->where(function ($inner) use ($search) {
                    $inner->where('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('mobile_number', 'like', "%{$search}%");
                })->pluck('id'))
                ->orWhereIn('document_id', \App\Models\Documents::query()->where('document_name', 'like', "%{$search}%")->pluck('id'));
        });
    }

    protected function authorizeDriverDocument(DriverDocuments $document): void
    {
        if (LOGIN_USER_TYPE == 'company') {
            $user = User::withTrashed()->find($document->user_id);
            if (!$user || (int) $user->company_id !== (int) Auth::guard('company')->id()) {
                abort(403);
            }
        }
    }

    protected function reviewerId(): ?int
    {
        if (LOGIN_USER_TYPE == 'company') {
            return (int) Auth::guard('company')->id();
        }

        return (int) Auth::guard('admin')->id();
    }

    protected function syncDriverAfterReview(int $userId): void
    {
        $user = User::find($userId);
        if (!$user) {
            return;
        }

        $user->status = UserStatusUpdate($user);
        $user->save();
    }

    protected function syncVehicleAfterReview($vehicleId): void
    {
        $vehicleId = (int) $vehicleId;
        if ($vehicleId <= 0) {
            return;
        }

        $vehicle = Vehicle::find($vehicleId);
        if (!$vehicle) {
            return;
        }

        $user = User::find($vehicle->user_id);
        if (!$user) {
            return;
        }

        $requiredDocuments = CheckDocument('Vehicle', $user->country_id ?: 'all');
        $documents = DriverDocuments::query()
            ->where('type', 'Vehicle')
            ->where('vehicle_id', $vehicleId)
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('document_id');

        $today = Carbon::today();
        $isApproved = $requiredDocuments->every(function ($required) use ($documents, $today) {
            $uploaded = $documents->get($required->id);
            if (!$uploaded || !$uploaded->document || (string) $uploaded->status !== '1') {
                return false;
            }

            if (!empty($uploaded->expired_date) && Carbon::parse($uploaded->expired_date)->lt($today)) {
                return false;
            }

            return true;
        });

        $vehicle->is_active = $isApproved ? 1 : 0;
        $vehicle->status = $isApproved ? 'Active' : 'Inactive';
        if (!$isApproved) {
            $vehicle->default_type = '0';
        }
        $vehicle->save();

        Vehicle::ensureDefaultForUser($user->id);
        $user->status = UserStatusUpdate($user);
        $user->save();
    }
}
