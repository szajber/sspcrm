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
        Schema::table('fire_extinguishers', function (Blueprint $table) {
            $table->year('next_service_year')->nullable()->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fire_extinguishers', function (Blueprint $table) {
            $table->dropColumn('next_service_year');
        });
    }
};
