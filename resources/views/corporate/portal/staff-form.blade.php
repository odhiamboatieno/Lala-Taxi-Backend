@php
    $suffix = $staffUser?->id ?? 'new';
    $useOld = old('_portal_form') === (string) $suffix;
@endphp
<form method="POST" action="{{ $staffAction }}" class="portal-form portal-form--grid row g-3 mb-3">
    @csrf
    @if($staffUser) @method('PUT') @endif
    <input type="hidden" name="_portal_form" value="{{ $suffix }}">
    <label class="col-md-6 form-label" for="staff_name_{{ $suffix }}">
        {{ __('corporate.portal_name') }}
        <input id="staff_name_{{ $suffix }}" class="form-control" type="text" name="name" required maxlength="255" value="{{ $useOld ? old('name') : $staffUser?->name }}">
    </label>
    <label class="col-md-6 form-label" for="staff_email_{{ $suffix }}">
        {{ __('corporate.portal_email') }}
        <input id="staff_email_{{ $suffix }}" class="form-control" type="email" name="email" required maxlength="255" value="{{ $useOld ? old('email') : $staffUser?->email }}">
    </label>
    <label class="col-md-6 form-label" for="staff_role_{{ $suffix }}">
        {{ __('corporate.portal_role') }}
        <select id="staff_role_{{ $suffix }}" class="form-select form-control" name="role">
            @foreach(['admin','finance','viewer'] as $portalRole)
                <option value="{{ $portalRole }}" @selected(($useOld ? old('role') : ($staffUser?->role ?? 'admin')) === $portalRole)>{{ __('corporate.portal_roles.'.$portalRole) }}</option>
            @endforeach
        </select>
    </label>
    <label class="col-md-6 form-label" for="staff_status_{{ $suffix }}">
        {{ __('corporate.status') }}
        <select id="staff_status_{{ $suffix }}" class="form-select form-control" name="status">
            @foreach(['Active','Inactive'] as $staffStatus)
                <option value="{{ $staffStatus }}" @selected(($useOld ? old('status') : ($staffUser?->status ?? 'Active')) === $staffStatus)>{{ __('corporate.states.'.$staffStatus) }}</option>
            @endforeach
        </select>
    </label>
    <div class="col-12"><button class="portal-button portal-button--gold btn btn-primary" type="submit">{{ __($staffUser ? 'corporate.save' : 'corporate.portal_invite') }}</button></div>
</form>
