<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('company_network_partners', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('company_id');
            $table->unsignedInteger('partner_company_id');
            $table->string('status', 191)->default('Active');
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['company_id', 'partner_company_id'], 'company_network_partners_company_id_partner_company_id_unique');
            $table->index(['company_id', 'status'], 'company_network_partners_company_id_status_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('company_network_partners');
    }
};
