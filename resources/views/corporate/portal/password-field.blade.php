<label for="{{ $passwordName }}">
    {{ __($passwordName === 'password_confirmation' ? 'corporate.portal_password_confirm' : 'corporate.portal_password') }}
    <span class="portal-password">
        <input id="{{ $passwordName }}" class="form-control" type="password" name="{{ $passwordName }}" autocomplete="{{ $newPassword ? 'new-password' : 'current-password' }}" required @if($newPassword) minlength="12" @endif maxlength="255">
        <button class="portal-password-toggle" type="button" data-password-toggle="{{ $passwordName }}" aria-controls="{{ $passwordName }}" aria-label="{{ __('corporate.portal_show_password') }}" data-show-label="{{ __('corporate.portal_show_password') }}" data-hide-label="{{ __('corporate.portal_hide_password') }}" aria-pressed="false" hidden><i data-lucide="eye" aria-hidden="true"></i></button>
    </span>
</label>
