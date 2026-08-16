@php
    $selectedRole = old('role', optional(optional($result)->roles->first())->id);
    $selectedCountry = old('country_code', optional($result)->country_id);
    $isEdit = !is_null($result);
@endphp

<div class="col-xl-6">
    <label class="form-label" for="input_username">
        Username <em class="text-danger">*</em>
    </label>
    {!! Form::text('username', old('username', optional($result)->username), [
        'class' => 'form-control',
        'id' => 'input_username',
        'placeholder' => 'Username',
    ]) !!}
    <span class="text-danger">{{ $errors->first('username') }}</span>
</div>

<div class="col-xl-6">
    <label class="form-label" for="input_email">
        Email <em class="text-danger">*</em>
    </label>
    {!! Form::email('email', old('email', optional($result)->email), [
        'class' => 'form-control',
        'id' => 'input_email',
        'placeholder' => 'Email',
    ]) !!}
    <span class="text-danger">{{ $errors->first('email') }}</span>
</div>

<div class="col-xl-4">
    <label class="form-label" for="input_country_code">
        Country Code <em class="text-danger">*</em>
    </label>
    {!! Form::select('country_code', $countries, $selectedCountry, [
        'class' => 'form-select',
        'id' => 'input_country_code',
        'placeholder' => 'Select country code',
    ]) !!}
    <span class="text-danger">{{ $errors->first('country_code') }}</span>
</div>

<div class="col-xl-8">
    <label class="form-label" for="input_mobile_number">
        Mobile Number <em class="text-danger">*</em>
    </label>
    {!! Form::text('mobile_number', old('mobile_number', optional($result)->mobile_number), [
        'class' => 'form-control',
        'id' => 'input_mobile_number',
        'placeholder' => 'Mobile Number',
    ]) !!}
    <span class="text-danger">{{ $errors->first('mobile_number') }}</span>
</div>

<div class="col-xl-6">
    <label class="form-label" for="input_role">
        {{ __('messages.admin.manage_admin_page.form.role') }} <em class="text-danger">*</em>
    </label>
    {!! Form::select('role', $roles, $selectedRole, [
        'class' => 'form-select',
        'id' => 'input_role',
        'placeholder' => 'Select role',
    ]) !!}
    <span class="text-danger">{{ $errors->first('role') }}</span>
</div>

<div class="col-xl-6">
    <label class="form-label" for="input_status">
        {{ __('messages.admin.manage_admin_page.form.status') }} <em class="text-danger">*</em>
    </label>
    {!! Form::select('status', ['Active' => 'Active', 'Inactive' => 'Inactive'], old('status', optional($result)->status), [
        'class' => 'form-select',
        'id' => 'input_status',
        'placeholder' => 'Select status',
    ]) !!}
    <span class="text-danger">{{ $errors->first('status') }}</span>
</div>

<div class="col-12">
    <label class="form-label" for="input_password">
        Password @if(!$isEdit)<em class="text-danger">*</em>@endif
    </label>
    {!! Form::password('password', [
        'class' => 'form-control',
        'id' => 'input_password',
        'placeholder' => $isEdit ? 'Leave blank to keep current password' : 'Password',
    ]) !!}
    <span class="text-danger">{{ $errors->first('password') }}</span>
    @if($isEdit)
        <small class="text-muted d-block mt-1">Leave blank to keep the current password.</small>
    @endif
</div>

<div class="col-12 mt-2">
    <p class="text-danger mb-0">(*) {{ __('messages.admin.fields_are_mandatory') }}</p>
</div>

<div class="col-12 d-flex justify-content-end gap-2 pt-2">
    <a href="{{ url(LOGIN_USER_TYPE . '/admin_user') }}" class="btn btn-light">{{ __('messages.admin.cancel') }}</a>
    <button type="submit" name="submit" value="submit" class="btn btn-primary">{{ __('messages.admin.submit') }}</button>
</div>
