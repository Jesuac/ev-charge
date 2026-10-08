<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meter_readings', function (Blueprint $table) {
            $table->id();
            $table->date('read_at')->unique();
            $table->decimal('reading', 12, 3);
            $table->string('notes', 255)->nullable();
            $table->timestamps();
        });

        $meterStart = (float) DB::table('settings')->value('meter_start');
        $earliestCharge = DB::table('charges')->min('charged_at');

        if ($meterStart > 0 || $earliestCharge !== null) {
            DB::table('meter_readings')->insert([
                'read_at' => $earliestCharge === null
                    ? Carbon::today()->toDateString()
                    : Carbon::parse($earliestCharge)->subDay()->toDateString(),
                'reading' => $meterStart,
                'notes' => 'Starting reading',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('meter_start');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->decimal('meter_start', 12, 3)->default(0);
        });

        DB::table('settings')->update([
            'meter_start' => DB::table('meter_readings')->orderBy('read_at')->value('reading') ?? 0,
        ]);

        Schema::dropIfExists('meter_readings');
    }
};
