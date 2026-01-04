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
        // 1. Słownik typów central oddymiania
        Schema::create('smoke_extraction_central_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Inwentaryzacja systemów oddymiania
        Schema::create('smoke_extraction_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained('client_objects')->onDelete('cascade');
            $table->foreignId('central_type_id')->nullable()->constrained('smoke_extraction_central_types')->onDelete('set null');
            $table->string('custom_central_type')->nullable(); // Jeśli nie wybrano ze słownika
            $table->string('location')->nullable();

            // Parametry ilościowe
            $table->string('detectors_count')->nullable(); // Ilość czujek w systemie lub sterowane z SSP
            $table->integer('buttons_count')->default(0); // Ilość przycisków w systemie
            $table->integer('vent_buttons_count')->default(0); // Ilość przycisków przewietrzania
            $table->integer('air_inlet_count')->default(0); // Ilość klap napowietrzających lub wentylatorów napowietrzających
            $table->integer('smoke_exhaust_count')->default(0); // Ilość klap oddymiających lub wentylatorów oddymiających

            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Pozycje w protokole
        Schema::create('protocol_smoke_extraction_systems', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained('protocols')->onDelete('cascade');
            $table->foreignId('smoke_extraction_system_id')->constrained('smoke_extraction_systems', 'id', 'proto_smoke_sys_fk')->onDelete('cascade');

            // Snapshot danych
            $table->string('central_type_name')->nullable();
            $table->string('location')->nullable();
            $table->string('detectors_count')->nullable();
            $table->integer('buttons_count')->default(0);
            $table->integer('vent_buttons_count')->default(0);
            $table->integer('air_inlet_count')->default(0);
            $table->integer('smoke_exhaust_count')->default(0);

            // Pomiary / Sprawdzenia
            $table->string('battery_date')->nullable(); // Data ważności akumulatorów

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
        Schema::dropIfExists('protocol_smoke_extraction_systems');
        Schema::dropIfExists('smoke_extraction_systems');
        Schema::dropIfExists('smoke_extraction_central_types');
    }
};
