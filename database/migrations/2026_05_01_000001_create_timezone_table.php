<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTimezoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('timezone')) {
            return;
        }

        Schema::create('timezone', function (Blueprint $table) {
            $table->increments('id');
            $table->string('timezone', 100)->unique();
        });

        $timezones = array_map(function ($timezone) {
            return ['timezone' => $timezone];
        }, DateTimeZone::listIdentifiers());

        foreach (array_chunk($timezones, 100) as $chunk) {
            DB::table('timezone')->insert($chunk);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('timezone');
    }
}
