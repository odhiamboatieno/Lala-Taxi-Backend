<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telescope_entries', function (Blueprint $table) {
            $table->bigIncrements('sequence');
            $table->char('uuid', 36);
            $table->char('batch_id', 36);
            $table->string('family_hash', 191)->nullable();
            $table->tinyInteger('should_display_on_index')->default('1');
            $table->string('type', 20);
            $table->longText('content');
            $table->dateTime('created_at')->nullable();
            $table->unique('uuid', 'telescope_entries_uuid_unique');
            $table->index('batch_id', 'telescope_entries_batch_id_index');
            $table->index('family_hash', 'telescope_entries_family_hash_index');
            $table->index('created_at', 'telescope_entries_created_at_index');
            $table->index(['type', 'should_display_on_index'], 'telescope_entries_type_should_display_on_index_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telescope_entries');
    }
};
