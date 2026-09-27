<?php

namespace Tests\Feature;

use App\Models\OtpVerification;
use App\Services\Otp\OtpManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OtpVerificationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createOtpTestSchema();
        $this->bindSiteSettings([
            'otp_verification' => '1',
            'demo_mode'        => '0',
            'otp_sandbox'      => '0',
        ]);
    }

    public function test_correct_otp_is_accepted(): void
    {
        $this->issueOtp('0797049289', '254', '010931');

        $response = $this->manager()->checkOtp('010931', '0797049289', '254');

        $this->assertSame(1, $response['status_code']);

        $this->assertNotNull(
            OtpVerification::where('mobile_number', '0797049289')->first()->verified_at
        );
    }

    public function test_wrong_otp_is_rejected_and_recorded_as_an_attempt(): void
    {
        $this->issueOtp('0797049289', '254', '010931');

        $response = $this->manager()->checkOtp('000000', '0797049289', '254');

        $this->assertSame(0, $response['status_code']);
        $this->assertSame('OTP verification failed. Please try again.', $response['message']);
        $this->assertSame(1, OtpVerification::where('mobile_number', '0797049289')->first()->attempts);
    }

    public function test_record_is_burned_after_max_attempts(): void
    {
        config(['otp.max_attempts' => 3]);
        $this->issueOtp('0797049289', '254', '010931');

        $this->manager()->checkOtp('000001', '0797049289', '254');
        $this->manager()->checkOtp('000002', '0797049289', '254');
        $response = $this->manager()->checkOtp('000003', '0797049289', '254');

        $this->assertSame(0, $response['status_code']);
        $this->assertSame('Too many incorrect attempts. Please request a new code.', $response['message']);

        // The issued code is destroyed, so even the real value no longer works.
        $this->assertSame(0, OtpVerification::where('mobile_number', '0797049289')->count());
        $this->assertSame(
            0,
            $this->manager()->checkOtp('010931', '0797049289', '254')['status_code']
        );
    }

    public function test_expired_otp_is_rejected(): void
    {
        $this->issueOtp('0797049289', '254', '010931', now()->subMinute());

        $response = $this->manager()->checkOtp('010931', '0797049289', '254');

        $this->assertSame(0, $response['status_code']);
        $this->assertSame('OTP has expired. Please request a new code.', $response['message']);
    }

    /**
     * The apps send the ISO short name to numbervalidation and the dialling
     * code to otp_verification. If the Country row was missing when the code
     * was issued, sendOtp() stored the raw short name, so the lookup key no
     * longer matches and the fallback has to find the record anyway.
     */
    public function test_lookup_falls_back_when_country_code_does_not_match(): void
    {
        $this->issueOtp('0797049289', 'KE', '010931');

        $this->assertSame(0, OtpVerification::where('country_code', '254')->count());

        $response = $this->manager()->checkOtp('010931', '0797049289', '254');

        $this->assertSame(1, $response['status_code']);
    }

    public function test_missing_record_is_reported(): void
    {
        $response = $this->manager()->checkOtp('010931', '0797049290', '254');

        $this->assertSame(0, $response['status_code']);
        $this->assertSame('OTP not found. Please request a new code.', $response['message']);
    }

    public function test_demo_mode_issues_the_fixed_code(): void
    {
        $this->bindSiteSettings([
            'otp_verification' => '1',
            'demo_mode'        => '1',
            'otp_sandbox'      => '0',
        ]);

        $response = $this->manager()->sendOtp('0797049289', '254');

        $this->assertSame(1, $response['status_code']);
        $this->assertSame('123456', $response['signup_otp']);
        $this->assertSame('123456', OtpVerification::where('mobile_number', '0797049289')->first()->otp);
    }

    public function test_sending_a_new_otp_replaces_the_previous_one(): void
    {
        $this->bindSiteSettings([
            'otp_verification' => '1',
            'demo_mode'        => '1',
            'otp_sandbox'      => '0',
        ]);

        $this->manager()->sendOtp('0797049289', '254');
        OtpVerification::where('mobile_number', '0797049289')->first()->forceFill([
            'otp'      => '654321',
            'attempts' => 4,
        ])->save();

        $this->manager()->sendOtp('0797049289', '254');

        $this->assertSame(1, OtpVerification::where('mobile_number', '0797049289')->count());

        $record = OtpVerification::where('mobile_number', '0797049289')->first();
        $this->assertSame('123456', $record->otp);
        $this->assertSame(0, $record->attempts);
    }

    protected function manager(): OtpManager
    {
        return resolve(OtpManager::class);
    }

    protected function issueOtp(string $mobile, string $country, string $otp, $expiresAt = null): OtpVerification
    {
        return OtpVerification::create([
            'mobile_number' => $mobile,
            'country_code'  => $country,
            'otp'           => $otp,
            'purpose'       => 'general',
            'attempts'      => 0,
            'expires_at'    => $expiresAt ?: now()->addMinutes(10),
        ]);
    }

    /**
     * site_settings() reads the container binding first and accesses the row
     * as an object ($row->value), so the binding must hold objects rather
     * than arrays. Binding directly also sidesteps the static cache inside the
     * helper, keeping each test independent.
     */
    protected function bindSiteSettings(array $settings): void
    {
        $rows = collect($settings)->map(function ($value, $name) {
            return (object) ['name' => $name, 'value' => $value];
        })->values();

        $this->app->instance('site_settings', $rows);
    }

    protected function createOtpTestSchema(): void
    {
        Schema::dropIfExists('otp_verifications');

        Schema::create('otp_verifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('mobile_number', 30);
            $table->string('country_code', 20)->nullable();
            $table->string('otp', 6);
            $table->string('purpose', 50)->default('general');
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }
}
