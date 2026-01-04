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
        // 1. Słownik typów klap
        Schema::create('fire_damper_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Inwentaryzacja klap
        Schema::create('fire_dampers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained('client_objects')->onDelete('cascade');
            $table->foreignId('type_id')->nullable()->constrained('fire_damper_types')->onDelete('set null');
            $table->string('custom_type')->nullable(); // Jeśli nie wybrano ze słownika
            $table->string('location')->nullable();
            $table->string('manufacturer')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Pozycje w protokole
        Schema::create('protocol_fire_dampers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained('protocols')->onDelete('cascade');
            $table->foreignId('fire_damper_id')->constrained('fire_dampers')->onDelete('cascade');

            // Snapshot danych
            $table->string('type_name')->nullable();
            $table->string('location')->nullable();
            $table->string('manufacturer')->nullable();

            // Pomiary / Sprawdzenia (tak/nie)
            $table->boolean('check_optical')->default(false);      // Optyczna kontrola urządzeń
            $table->boolean('check_drive')->default(false);        // Sprawdzenie napędu mechanicznego
            $table->boolean('check_mechanical')->default(false);   // Sprawdzenie części mechanicznych
            $table->boolean('check_alarm')->default(false);        // Sprawdzenie zadziałania w trybie alarmowym

            // Wynik i uwagi
            $table->enum('result', ['positive', 'negative'])->default('positive'); // Wynik (pozytywny/negatywny)
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_fire_dampers');
        Schema::dropIfExists('fire_dampers');
        Schema::dropIfExists('fire_damper_types');
    }
};
