<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCompanyDriverFeeSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('company_driver_fee_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('company_id');
            $table->enum('fee_type', ['percentage', 'weekly_subscription', 'monthly_subscription'])->default('percentage');
            $table->decimal('amount', 11, 2)->default(0);
            $table->enum('status', ['Active', 'Inactive'])->default('Inactive');
            $table->string('title')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('company_id');
            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
        });

        Schema::table('trips', function (Blueprint $table) {
            $table->decimal('platform_driver_fee_amount', 11, 2)->default(0)->after('driver_or_company_commission');
            $table->decimal('company_commission_amount', 11, 2)->default(0)->after('platform_driver_fee_amount');
            $table->decimal('company_driver_fee_amount', 11, 2)->default(0)->after('company_commission_amount');
            $table->string('company_driver_fee_type')->nullable()->after('company_driver_fee_amount');
            $table->string('company_driver_fee_label')->nullable()->after('company_driver_fee_type');
        });
    }

    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            $table->dropColumn([
                'platform_driver_fee_amount',
                'company_commission_amount',
                'company_driver_fee_amount',
                'company_driver_fee_type',
                'company_driver_fee_label',
            ]);
        });

        Schema::dropIfExists('company_driver_fee_settings');
    }
}
