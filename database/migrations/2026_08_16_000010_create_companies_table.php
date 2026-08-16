<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('companies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('legal_name', 150)->nullable();
            $table->string('trading_name', 150)->nullable();
            $table->string('profile', 100);
            $table->string('email', 100);
            $table->string('country_code', 100);
            $table->string('mobile_number', 20);
            $table->string('alternative_phone', 30)->nullable();
            $table->string('vat_number', 100)->nullable();
            $table->string('registration_number', 120)->nullable();
            $table->string('business_type', 80)->nullable();
            $table->unsignedInteger('vehicles_count')->default('0');
            $table->unsignedInteger('drivers_count')->default('0');
            $table->string('service_area', 150)->nullable();
            $table->string('website_url', 255)->nullable();
            $table->text('short_description')->nullable();
            $table->string('contact_first_name', 100)->nullable();
            $table->string('contact_last_name', 100)->nullable();
            $table->string('contact_position', 100)->nullable();
            $table->string('password', 191);
            $table->string('remember_token', 100)->nullable();
            $table->enum('status', ['Pending', 'Active', 'Inactive'])->default('Pending');
            $table->string('onboarding_status', 50)->default('draft');
            $table->string('approval_status', 50)->default('pending_approval');
            $table->timestamp('approved_at')->nullable();
            $table->unsignedInteger('approved_by')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->unsignedInteger('rejected_by')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submitted_for_approval_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->unsignedTinyInteger('onboarding_step')->default('1');
            $table->string('registration_reference', 64)->nullable();
            $table->text('admin_notes')->nullable();
            $table->longText('dispatch_settings')->nullable();
            $table->enum('device_type', ['1', '2'])->nullable();
            $table->text('device_id');
            $table->string('language', 50);
            $table->string('address', 191)->nullable();
            $table->string('office_building_name', 150)->nullable();
            $table->string('city', 191)->nullable();
            $table->string('state', 191)->nullable();
            $table->string('country', 100);
            $table->string('postal_code', 191)->nullable();
            $table->string('map_coordinates', 120)->nullable();
            $table->string('company_commission', 5)->default('0');
            $table->unsignedInteger('country_id')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->index('country_id', 'companies_country_id_foreign');
            $table->index('status', 'companies_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('companies');
    }
};
