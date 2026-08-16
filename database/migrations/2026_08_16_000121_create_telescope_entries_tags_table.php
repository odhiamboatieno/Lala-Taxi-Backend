<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telescope_entries_tags', function (Blueprint $table) {
            $table->char('entry_uuid', 36);
            $table->string('tag', 191);
            $table->index(['entry_uuid', 'tag'], 'telescope_entries_tags_entry_uuid_tag_index');
            $table->index('tag', 'telescope_entries_tags_tag_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telescope_entries_tags');
    }
};
