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
        Schema::table('protocol_ventilation_distributors', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('location');
        });

        Schema::table('protocol_ventilation_fans', function (Blueprint $table) {
            $table->integer('sort_order')->default(0)->after('location');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocol_ventilation_distributors', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });

        Schema::table('protocol_ventilation_fans', function (Blueprint $table) {
            $table->dropColumn('sort_order');
        });
    }
};
