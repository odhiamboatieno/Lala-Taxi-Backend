<?php

use App\Models\SiteSettings;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompanyOnboardingSupport extends Migration
{
    public function up()
    {
        if (Schema::hasTable('companies')) {
            Schema::table('companies', function (Blueprint $table) {
                if (! Schema::hasColumn('companies', 'legal_name')) {
                    $table->string('legal_name', 150)->nullable()->after('name');
                }
                if (! Schema::hasColumn('companies', 'trading_name')) {
                    $table->string('trading_name', 150)->nullable()->after('legal_name');
                }
                if (! Schema::hasColumn('companies', 'registration_number')) {
                    $table->string('registration_number', 120)->nullable()->after('vat_number');
                }
                if (! Schema::hasColumn('companies', 'business_type')) {
                    $table->string('business_type', 80)->nullable()->after('registration_number');
                }
                if (! Schema::hasColumn('companies', 'vehicles_count')) {
                    $table->unsignedInteger('vehicles_count')->default(0)->after('business_type');
                }
                if (! Schema::hasColumn('companies', 'drivers_count')) {
                    $table->unsignedInteger('drivers_count')->default(0)->after('vehicles_count');
                }
                if (! Schema::hasColumn('companies', 'service_area')) {
                    $table->string('service_area', 150)->nullable()->after('drivers_count');
                }
                if (! Schema::hasColumn('companies', 'website_url')) {
                    $table->string('website_url', 255)->nullable()->after('service_area');
                }
                if (! Schema::hasColumn('companies', 'short_description')) {
                    $table->text('short_description')->nullable()->after('website_url');
                }
                if (! Schema::hasColumn('companies', 'contact_first_name')) {
                    $table->string('contact_first_name', 100)->nullable()->after('short_description');
                }
                if (! Schema::hasColumn('companies', 'contact_last_name')) {
                    $table->string('contact_last_name', 100)->nullable()->after('contact_first_name');
                }
                if (! Schema::hasColumn('companies', 'contact_position')) {
                    $table->string('contact_position', 100)->nullable()->after('contact_last_name');
                }
                if (! Schema::hasColumn('companies', 'alternative_phone')) {
                    $table->string('alternative_phone', 30)->nullable()->after('mobile_number');
                }
                if (! Schema::hasColumn('companies', 'office_building_name')) {
                    $table->string('office_building_name', 150)->nullable()->after('address');
                }
                if (! Schema::hasColumn('companies', 'map_coordinates')) {
                    $table->string('map_coordinates', 120)->nullable()->after('postal_code');
                }
                if (! Schema::hasColumn('companies', 'onboarding_status')) {
                    $table->string('onboarding_status', 50)->default('draft')->after('status');
                }
                if (! Schema::hasColumn('companies', 'approval_status')) {
                    $table->string('approval_status', 50)->default('pending_approval')->after('onboarding_status');
                }
                if (! Schema::hasColumn('companies', 'approved_at')) {
                    $table->timestamp('approved_at')->nullable()->after('approval_status');
                }
                if (! Schema::hasColumn('companies', 'approved_by')) {
                    $table->unsignedInteger('approved_by')->nullable()->after('approved_at');
                }
                if (! Schema::hasColumn('companies', 'rejected_at')) {
                    $table->timestamp('rejected_at')->nullable()->after('approved_by');
                }
                if (! Schema::hasColumn('companies', 'rejected_by')) {
                    $table->unsignedInteger('rejected_by')->nullable()->after('rejected_at');
                }
                if (! Schema::hasColumn('companies', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('rejected_by');
                }
                if (! Schema::hasColumn('companies', 'submitted_for_approval_at')) {
                    $table->timestamp('submitted_for_approval_at')->nullable()->after('rejection_reason');
                }
                if (! Schema::hasColumn('companies', 'phone_verified_at')) {
                    $table->timestamp('phone_verified_at')->nullable()->after('submitted_for_approval_at');
                }
                if (! Schema::hasColumn('companies', 'email_verified_at')) {
                    $table->timestamp('email_verified_at')->nullable()->after('phone_verified_at');
                }
                if (! Schema::hasColumn('companies', 'onboarding_step')) {
                    $table->unsignedTinyInteger('onboarding_step')->default(1)->after('email_verified_at');
                }
                if (! Schema::hasColumn('companies', 'registration_reference')) {
                    $table->string('registration_reference', 64)->nullable()->after('onboarding_step');
                }
                if (! Schema::hasColumn('companies', 'admin_notes')) {
                    $table->text('admin_notes')->nullable()->after('registration_reference');
                }
            });
        }

        if (Schema::hasTable('company_documents')) {
            Schema::table('company_documents', function (Blueprint $table) {
                if (! Schema::hasColumn('company_documents', 'original_name')) {
                    $table->string('original_name', 255)->nullable()->after('document');
                }
                if (! Schema::hasColumn('company_documents', 'mime_type')) {
                    $table->string('mime_type', 120)->nullable()->after('original_name');
                }
                if (! Schema::hasColumn('company_documents', 'file_size')) {
                    $table->unsignedBigInteger('file_size')->nullable()->after('mime_type');
                }
                if (! Schema::hasColumn('company_documents', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('status');
                }
                if (! Schema::hasColumn('company_documents', 'uploaded_at')) {
                    $table->timestamp('uploaded_at')->nullable()->after('rejection_reason');
                }
                if (! Schema::hasColumn('company_documents', 'reviewed_at')) {
                    $table->timestamp('reviewed_at')->nullable()->after('uploaded_at');
                }
                if (! Schema::hasColumn('company_documents', 'reviewed_by')) {
                    $table->unsignedInteger('reviewed_by')->nullable()->after('reviewed_at');
                }
            });
        }

        if (! Schema::hasTable('company_verification_otps')) {
            Schema::create('company_verification_otps', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('type', 20);
                $table->string('otp', 20);
                $table->timestamp('expires_at');
                $table->timestamp('verified_at')->nullable();
                $table->unsignedInteger('attempts')->default(0);
                $table->timestamps();

                $table->index(['company_id', 'type']);
                $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            });
        }

        foreach ([
            'company_onboarding_enabled' => '1',
            'company_onboarding_require_phone_verification' => '1',
            'company_onboarding_require_email_verification' => '1',
            'company_onboarding_require_logo_upload' => '1',
            'company_onboarding_require_documents_upload' => '1',
            'company_onboarding_require_admin_approval' => '1',
        ] as $name => $value) {
            SiteSettings::updateOrCreate(['name' => $name], ['value' => $value]);
        }
    }

    public function down()
    {
        if (Schema::hasTable('company_verification_otps')) {
            Schema::dropIfExists('company_verification_otps');
        }

        foreach ([
            'company_onboarding_enabled',
            'company_onboarding_require_phone_verification',
            'company_onboarding_require_email_verification',
            'company_onboarding_require_logo_upload',
            'company_onboarding_require_documents_upload',
            'company_onboarding_require_admin_approval',
        ] as $name) {
            SiteSettings::where('name', $name)->delete();
        }
    }
}
