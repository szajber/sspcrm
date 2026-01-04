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
        Schema::table('protocol_fire_extinguishers', function (Blueprint $table) {
            $table->dropColumn('next_service_date');
            $table->year('next_service_year')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocol_fire_extinguishers', function (Blueprint $table) {
            $table->dropColumn('next_service_year');
            $table->date('next_service_date')->nullable()->after('status');
        });
    }
};
