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
            $table->boolean('check_test_button')->nullable()->after('check_buttons'); // Przycisk testowy
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocol_fire_gate_devices', function (Blueprint $table) {
            $table->dropColumn('check_test_button');
        });
    }
};
