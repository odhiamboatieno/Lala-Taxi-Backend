@extends('admin.template')

@section('main')
<div class="content-wrapper">
    <section class="content-header">
        <h1>{{ $member->exists ? 'Edit Employee' : 'Add New Employee' }}</h1>
    </section>
    <section class="content">
        <div class="card">
            <div class="card-body">
                <form method="post" enctype="multipart/form-data" action="{{ $member->exists ? url('company/operation-team/members/'.$member->id) : url('company/operation-team/members') }}">
                    @csrf
                    @if($member->exists)
                        @method('PUT')
                    @endif
                    <div class="row g-3">
                        <div class="col-md-6"><label class="form-label">First Name</label><input type="text" name="first_name" class="form-control" value="{{ old('first_name', $member->first_name) }}" required></div>
                        <div class="col-md-6"><label class="form-label">Last Name</label><input type="text" name="last_name" class="form-control" value="{{ old('last_name', $member->last_name) }}"></div>
                        <div class="col-md-6"><label class="form-label">Email</label><input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}"></div>
                        <div class="col-md-3"><label class="form-label">Country Code</label><input type="text" name="country_code" class="form-control" value="{{ old('country_code', $member->country_code) }}"></div>
                        <div class="col-md-3"><label class="form-label">Phone</label><input type="text" name="mobile_number" class="form-control" value="{{ old('mobile_number', $member->mobile_number) }}"></div>
                        <div class="col-md-6"><label class="form-label">Address</label><input type="text" name="address" class="form-control" value="{{ old('address', $member->address) }}"></div>
                        <div class="col-md-3"><label class="form-label">Identity Type</label><input type="text" name="identity_type" class="form-control" value="{{ old('identity_type', $member->identity_type) }}"></div>
                        <div class="col-md-3"><label class="form-label">Identity Number</label><input type="text" name="identity_number" class="form-control" value="{{ old('identity_number', $member->identity_number) }}"></div>
                        <div class="col-md-6"><label class="form-label">Employee Role</label><select name="role_id" class="form-control" required><option value="">Select role</option>@foreach($roles as $role)<option value="{{ $role->id }}" {{ (string) old('role_id', $member->role_id) === (string) $role->id ? 'selected' : '' }}>{{ $role->name }}</option>@endforeach</select></div>
                        <div class="col-md-3"><label class="form-label">Password</label><input type="password" name="password" class="form-control" {{ $member->exists ? '' : 'required' }}></div>
                        <div class="col-md-3"><label class="form-label">Employee Image</label><input type="file" name="image" class="form-control"></div>
                        <div class="col-md-6"><label class="form-label">Identity Image</label><input type="file" name="identity_image" class="form-control"></div>
                    </div>
                    <div class="form-check form-switch my-4">
                        <input type="checkbox" class="form-check-input" name="status" value="1" {{ old('status', $member->status ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label">Active</label>
                    </div>
                    <div class="text-end">
                        <a href="{{ url('company/operation-team/members') }}" class="btn btn-secondary">Cancel</a>
                        <button class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>
@endsection
