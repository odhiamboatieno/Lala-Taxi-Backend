<?php

namespace App\Http\Controllers\Corporate;

use App\Http\Controllers\Controller;
use App\Models\CorporatePortalUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Illuminate\Validation\ValidationException;

class PortalAuthController extends Controller
{
    public function loginForm() { return response()->view('corporate.portal.login')->header('Cache-Control', 'private, no-store'); }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email:rfc|max:255', 'password' => 'required|string|max:255']);
        $data['email'] = Str::lower(trim($data['email']));
        $data['status'] = 'Active';
        if (!Auth::guard('corporate')->attempt($data)) {
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }
        $user = Auth::guard('corporate')->user();
        if (!$user->password_set_at || !in_array($user->role, ['admin', 'finance', 'viewer'], true)
            || !$user->account || ($user->account->status !== 'Active' && !$user->account->archived_at)) {
            Auth::guard('corporate')->logout();
            throw ValidationException::withMessages(['email' => __('auth.failed')]);
        }
        $request->session()->regenerate();
        $request->session()->put('corporate.session_version', $user->session_version);
        return redirect()->route('corporate.portal.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('corporate')->logout();
        $request->session()->forget('corporate.session_version');
        $request->session()->regenerate();
        $request->session()->regenerateToken();
        return redirect()->route('corporate.portal.login');
    }

    public function forgotForm() { return response()->view('corporate.portal.forgot')->header('Cache-Control', 'private, no-store'); }

    public function forgot(Request $request)
    {
        $data = $request->validate(['email' => 'required|email:rfc|max:255']);
        try {
            Password::broker('corporate')->sendResetLink(['email' => Str::lower(trim($data['email'])), 'status' => 'Active']);
        } catch (\Throwable $exception) {
            report($exception);
            // Do not disclose whether the email identifies portal staff.
        }
        return back()->with('status', __('corporate.portal_recovery_sent'));
    }

    public function resetForm(Request $request, string $token)
    {
        return response()->view('corporate.portal.reset', ['token' => $token, 'email' => $request->query('email', '')])
            ->header('Cache-Control', 'private, no-store');
    }

    public function reset(Request $request)
    {
        $data = $request->validate(['token' => 'required|string', 'email' => 'required|email:rfc|max:255',
            'password' => ['required', 'confirmed', 'max:255', PasswordRule::min(12)->letters()->numbers()]]);
        $data['email'] = Str::lower(trim($data['email']));
        $data['status'] = 'Active';
        $status = DB::transaction(function () use ($data) {
            // Serialize consumption so simultaneous requests cannot reuse one valid token.
            DB::table('corporate_password_resets')->where('email', $data['email'])->lockForUpdate()->first();
            return Password::broker('corporate')->reset($data, function (CorporatePortalUser $user, string $password) use ($data) {
                $user = CorporatePortalUser::lockForUpdate()->findOrFail($user->id);
                if ($user->email !== $data['email'] || $user->status !== 'Active') {
                    throw ValidationException::withMessages(['email' => __('passwords.token')]);
                }
                $user->forceFill(['password' => Hash::make($password), 'password_set_at' => now(),
                    'session_version' => $user->session_version + 1, 'remember_token' => Str::random(60)])->save();
            });
        }, 3);
        if ($status !== Password::PASSWORD_RESET) throw ValidationException::withMessages(['email' => __($status)]);
        return redirect()->route('corporate.portal.login')->with('status', __('passwords.reset'));
    }
}
