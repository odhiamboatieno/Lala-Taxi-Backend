<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_service_areas', function (Blueprint $table) {
            if (!Schema::hasColumn('company_service_areas', 'extra_fare_enabled')) {
                $table->boolean('extra_fare_enabled')->default(false)->after('dropoff_enabled');
            }
            if (!Schema::hasColumn('company_service_areas', 'extra_fare_percentage')) {
                $table->decimal('extra_fare_percentage', 8, 2)->default(0)->after('extra_fare_enabled');
            }
            if (!Schema::hasColumn('company_service_areas', 'extra_fare_reason')) {
                $table->string('extra_fare_reason')->nullable()->after('extra_fare_percentage');
            }
        });

        if (!Schema::hasTable('saas_feature_modules')) {
            Schema::create('saas_feature_modules', function (Blueprint $table) {
                $table->id();
                $table->string('slug')->unique();
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_installed')->default(false);
                $table->boolean('is_enabled')->default(false);
                $table->unsignedInteger('sort_order')->default(0);
                $table->string('status')->default('Inactive');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('company_network_partners')) {
            Schema::create('company_network_partners', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedInteger('partner_company_id');
                $table->string('status')->default('Active');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->unique(['company_id', 'partner_company_id']);
                $table->index(['company_id', 'status']);
            });
        }

        if (!Schema::hasTable('company_network_trip_shares')) {
            Schema::create('company_network_trip_shares', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('source_company_id');
                $table->unsignedInteger('target_company_id');
                $table->unsignedInteger('trip_id')->nullable();
                $table->unsignedInteger('request_id')->nullable();
                $table->string('status')->default('pending');
                $table->text('share_note')->nullable();
                $table->text('response_note')->nullable();
                $table->timestamp('shared_at')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->unsignedInteger('accepted_by_company_id')->nullable();
                $table->timestamps();
                $table->index(['source_company_id', 'status']);
                $table->index(['target_company_id', 'status']);
            });
        }

        if (!Schema::hasTable('company_corporate_accounts')) {
            Schema::create('company_corporate_accounts', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('account_name');
                $table->string('contact_name')->nullable();
                $table->string('contact_email')->nullable();
                $table->string('contact_phone')->nullable();
                $table->string('billing_cycle')->default('monthly');
                $table->decimal('credit_limit', 12, 2)->default(0);
                $table->string('status')->default('Active');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['company_id', 'status']);
            });
        }

        if (!Schema::hasTable('company_corporate_invoices')) {
            Schema::create('company_corporate_invoices', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->unsignedBigInteger('corporate_account_id');
                $table->string('invoice_number')->unique();
                $table->date('invoice_date');
                $table->date('due_date')->nullable();
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->string('status')->default('Draft');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['company_id', 'status']);
            });
        }

        if (!Schema::hasTable('company_message_threads')) {
            Schema::create('company_message_threads', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('subject');
                $table->string('participant_type')->default('staff');
                $table->unsignedInteger('participant_id')->nullable();
                $table->string('status')->default('open');
                $table->timestamp('last_message_at')->nullable();
                $table->boolean('green_flash')->default(false);
                $table->timestamps();
                $table->index(['company_id', 'status']);
            });
        }

        if (!Schema::hasTable('company_messages')) {
            Schema::create('company_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('thread_id');
                $table->string('sender_type')->default('company');
                $table->unsignedInteger('sender_id')->nullable();
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index(['thread_id', 'created_at']);
            });
        }

        if (!Schema::hasTable('saas_support_tickets')) {
            Schema::create('saas_support_tickets', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('company_id');
                $table->string('created_by_type')->default('company');
                $table->unsignedInteger('created_by_id')->nullable();
                $table->string('subject');
                $table->string('category')->default('general');
                $table->string('priority')->default('normal');
                $table->string('status')->default('open');
                $table->text('description')->nullable();
                $table->unsignedInteger('assigned_admin_id')->nullable();
                $table->timestamp('last_message_at')->nullable();
                $table->timestamp('resolved_at')->nullable();
                $table->timestamps();
                $table->index(['company_id', 'status']);
            });
        }

        if (!Schema::hasTable('saas_support_ticket_messages')) {
            Schema::create('saas_support_ticket_messages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('ticket_id');
                $table->string('sender_type')->default('company');
                $table->unsignedInteger('sender_id')->nullable();
                $table->text('message');
                $table->timestamp('read_at')->nullable();
                $table->timestamps();
                $table->index(['ticket_id', 'created_at']);
            });
        }

        foreach ($this->defaultModules() as $module) {
            $existing = DB::table('saas_feature_modules')->where('slug', $module['slug'])->first();

            if ($existing) {
                DB::table('saas_feature_modules')
                    ->where('slug', $module['slug'])
                    ->update(array_merge($module, ['updated_at' => now()]));
            } else {
                DB::table('saas_feature_modules')->insert(array_merge($module, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('saas_support_ticket_messages');
        Schema::dropIfExists('saas_support_tickets');
        Schema::dropIfExists('company_messages');
        Schema::dropIfExists('company_message_threads');
        Schema::dropIfExists('company_corporate_invoices');
        Schema::dropIfExists('company_corporate_accounts');
        Schema::dropIfExists('company_network_trip_shares');
        Schema::dropIfExists('company_network_partners');
        Schema::dropIfExists('saas_feature_modules');

        Schema::table('company_service_areas', function (Blueprint $table) {
            foreach (['extra_fare_enabled', 'extra_fare_percentage', 'extra_fare_reason'] as $column) {
                if (Schema::hasColumn('company_service_areas', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    protected function defaultModules(): array
    {
        return [
            ['slug' => 'company_documents', 'name' => 'Documents Management', 'description' => 'Manage company driver and vehicle documents.', 'is_installed' => true, 'is_enabled' => true, 'sort_order' => 10, 'status' => 'Active'],
            ['slug' => 'networking', 'name' => 'Networking', 'description' => 'Share overflow work with partner fleets or receive jobs from them.', 'is_installed' => true, 'is_enabled' => true, 'sort_order' => 20, 'status' => 'Active'],
            ['slug' => 'accounts_invoicing', 'name' => 'Accounts / Invoicing', 'description' => 'Corporate accounts, split invoices, and customer billing.', 'is_installed' => true, 'is_enabled' => true, 'sort_order' => 30, 'status' => 'Active'],
            ['slug' => 'data_suite', 'name' => 'Data Suite', 'description' => 'Fleet performance, passenger behavior, and earnings analytics.', 'is_installed' => true, 'is_enabled' => true, 'sort_order' => 40, 'status' => 'Active'],
            ['slug' => 'messages', 'name' => 'Messages', 'description' => 'Centralized communication hub for drivers and staff.', 'is_installed' => true, 'is_enabled' => true, 'sort_order' => 50, 'status' => 'Active'],
            ['slug' => 'support_system_status', 'name' => 'Support & System Status', 'description' => 'Advanced ticketing between companies and platform admin.', 'is_installed' => true, 'is_enabled' => true, 'sort_order' => 60, 'status' => 'Active'],
        ];
    }
};
