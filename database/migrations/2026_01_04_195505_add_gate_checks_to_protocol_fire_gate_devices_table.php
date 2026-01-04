<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('protocol_fire_gate_devices', function (Blueprint $table) {
            $table->boolean('check_counterweight')->nullable()->after('check_drive'); // Przeciwwaga
            $table->boolean('check_magnetic_clutch')->nullable()->after('check_counterweight'); // Sprzęgło magnetyczne
            // check_holding_mechanism (Trzymacz) i check_drive (Silnik) już istnieją
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocol_fire_gate_devices', function (Blueprint $table) {
            $table->dropColumn(['check_counterweight', 'check_magnetic_clutch']);
        });
    }
};
