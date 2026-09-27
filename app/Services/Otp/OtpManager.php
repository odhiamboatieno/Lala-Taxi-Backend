<?php

namespace App\Services\Otp;

use App\Models\Country;
use App\Models\OtpVerification;
use Carbon\Carbon;

class OtpManager
{
    /**
     * Send and persist OTP for a mobile number.
     *
     * @param string $mobile_number
     * @param string $country_code
     * @param string $purpose
     * @return array
     */
    public function sendOtp($mobile_number, $country_code, $purpose = 'general')
    {
        if (!$this->isOtpVerificationEnabled()) {
            return [
                'status_code' => 1,
                'message' => 'success',
                'signup_otp' => '',
            ];
        }

        $normalized = $this->normalizeCountryCode($country_code);
        $otp = $this->generateOtp();

        $this->logOtp('sent', $mobile_number, $otp, $purpose, [
            'demo' => $this->isDemoModeEnabled(),
            'dial' => $normalized['dial_code'],
        ]);

        $record = $this->storeOtp($mobile_number, $normalized['key'], $otp, $purpose);

        session([
            'signup_mobile' => $mobile_number,
            'signup_country_code' => $country_code,
            'signup_otp' => $otp,
        ]);

        if ($this->isDemoModeEnabled()) {
            return [
                'status_code' => 1,
                'message' => 'success',
                'signup_otp' => $otp,
            ];
        }

        $to = '+' . $normalized['dial_code'] . $mobile_number;
        $sms_gateway = resolve(\App\Contracts\SMSInterface::class);
        $sms_message = 'Your OTP number is ' . $otp;
        $response = $sms_gateway->send($to, $sms_message);

        if (($response['status_code'] ?? 0) != 1) {
            $record->delete();
            session()->forget('signup_otp');

            return [
                'status_code' => 0,
                'message' => $response['message'] ?? 'Unable to send OTP',
                'signup_otp' => '',
            ];
        }

        return [
            'status_code' => 1,
            'message' => 'success',
            'signup_otp' => $otp,
        ];
    }

    /**
     * Resend OTP using session-backed mobile details.
     *
     * @param string $purpose
     * @return array
     */
    public function resendOtp($purpose = 'general')
    {
        $mobile_number = session('signup_mobile');
        $country_code = session('signup_country_code');

        if (!$mobile_number || !$country_code) {
            return [
                'status_code' => 0,
                'message' => 'Mobile number is missing for OTP resend.',
                'signup_otp' => '',
            ];
        }

        $response = $this->sendOtp($mobile_number, $country_code, $purpose);
        if (($response['status_code'] ?? 0) == 1) {
            $response['message'] = trans('messages.signup.otp_resended');
        }

        return $response;
    }

    /**
     * Verify an OTP from the database.
     *
     * @param string $otp
     * @param string|null $mobile_number
     * @param string|null $country_code
     * @param string $purpose
     * @return array
     */
    public function checkOtp($otp, $mobile_number = null, $country_code = null, $purpose = 'general')
    {
        if (!$this->isOtpVerificationEnabled()) {
            return [
                'status_code' => 1,
                'message' => 'success',
            ];
        }

        $mobile_number = $mobile_number ?: session('signup_mobile');
        $country_code = $country_code ?: session('signup_country_code');

        if (!$mobile_number || !$country_code) {
            return [
                'status_code' => 0,
                'message' => 'OTP session has expired. Please request a new code.',
            ];
        }

        $normalized = $this->normalizeCountryCode($country_code);

        $query = OtpVerification::where('mobile_number', $mobile_number)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->latest('id');

        $record = (clone $query)->where('country_code', $normalized['key'])->first();

        // The apps send the ISO short name to one endpoint and the dialling code
        // to another, so the stored country_code may not match this lookup key.
        // Fall back to the most recent unverified code for the number.
        if (!$record) {
            $record = (clone $query)->first();
        }

        if (!$record) {
            return [
                'status_code' => 0,
                'message' => 'OTP not found. Please request a new code.',
            ];
        }

        if ($record->expires_at && Carbon::now()->greaterThan($record->expires_at)) {
            return [
                'status_code' => 0,
                'message' => 'OTP has expired. Please request a new code.',
            ];
        }

        if ((string) $record->otp !== (string) $otp) {
            $attempts = $this->registerFailedAttempt($record);

            $this->logOtp('verify-mismatch', $mobile_number, $record->otp, $purpose, [
                'submitted' => (string) $otp,
                'attempts'  => $attempts,
                'remaining' => max(0, $this->maxAttempts() - $attempts),
            ]);

            if ($attempts >= $this->maxAttempts()) {
                return [
                    'status_code' => 0,
                    'message' => 'Too many incorrect attempts. Please request a new code.',
                ];
            }

            return [
                'status_code' => 0,
                'message' => 'OTP verification failed. Please try again.',
            ];
        }

        $record->verified_at = Carbon::now();
        $record->save();

        return [
            'status_code' => 1,
            'message' => 'success',
        ];
    }

    /**
     * Determine if OTP verification is enabled.
     *
     * @return bool
     */
    protected function isOtpVerificationEnabled()
    {
        return site_settings('otp_verification') == '1';
    }

    /**
     * Determine if demo mode is enabled.
     *
     * @return bool
     */
    protected function isDemoModeEnabled()
    {
        return site_settings('demo_mode') == '1' || site_settings('otp_sandbox') == '1';
    }

    /**
     * Generate the OTP value.
     *
     * @return string
     */
    protected function generateOtp()
    {
        if ($this->isDemoModeEnabled()) {
            return '123456';
        }

        return str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Persist a newly generated OTP.
     *
     * @param string $mobile_number
     * @param string $country_code
     * @param string $otp
     * @param string $purpose
     * @return \App\Models\OtpVerification
     */
    protected function storeOtp($mobile_number, $country_code, $otp, $purpose)
    {
        OtpVerification::where('mobile_number', $mobile_number)
            ->where('country_code', $country_code)
            ->where('purpose', $purpose)
            ->whereNull('verified_at')
            ->delete();

        return OtpVerification::create([
            'mobile_number' => $mobile_number,
            'country_code' => $country_code,
            'otp' => $otp,
            'purpose' => $purpose,
            'expires_at' => Carbon::now()->addMinutes(10),
        ]);
    }

    /**
     * Record a wrong OTP against the issued record and return the new count.
     *
     * @param \App\Models\OtpVerification $record
     * @return int
     */
    protected function registerFailedAttempt($record)
    {
        $attempts = (int) ($record->attempts ?? 0) + 1;

        $record->attempts = $attempts;
        $record->save();

        // Burn the code once the allowance is used up so the remaining window
        // cannot be brute forced.
        if ($attempts >= $this->maxAttempts()) {
            $record->delete();
        }

        return $attempts;
    }

    /**
     * Maximum wrong attempts tolerated per issued OTP.
     *
     * @return int
     */
    protected function maxAttempts()
    {
        return max(1, (int) config('otp.max_attempts', 5));
    }

    /**
     * Write the OTP to the application log so it can be read from container
     * output (`docker compose logs app`). No-op unless OTP_LOG_ENABLED is true.
     *
     * @param string $event
     * @param string $mobile_number
     * @param string|null $otp
     * @param string $purpose
     * @param array $context
     * @return void
     */
    protected function logOtp($event, $mobile_number, $otp = null, $purpose = 'general', array $context = [])
    {
        if (!config('otp.log_enabled')) {
            return;
        }

        $parts = [
            'event'   => $event,
            'mobile'  => $mobile_number,
            'purpose' => $purpose,
        ];

        if ($otp !== null) {
            $parts['otp'] = $otp;
        }

        foreach ($context as $key => $value) {
            $parts[$key] = $value;
        }

        \Log::info('[OTP] ' . json_encode($parts));
    }

    /**
     * Normalize short-name and dialing-code country inputs.
     *
     * @param string $country_code
     * @return array
     */
    protected function normalizeCountryCode($country_code)
    {
        $country_code = trim((string) $country_code);
        $country_code = ltrim($country_code, '+');

        if ($country_code === '') {
            return ['key' => '', 'dial_code' => ''];
        }

        if (ctype_digit($country_code)) {
            return [
                'key' => $country_code,
                'dial_code' => $country_code,
            ];
        }

        $country = Country::whereShortName($country_code)->first();
        if ($country) {
            return [
                'key' => (string) $country->phone_code,
                'dial_code' => (string) $country->phone_code,
            ];
        }

        return [
            'key' => $country_code,
            'dial_code' => $country_code,
        ];
    }
}
