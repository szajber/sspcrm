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
        // 1. Inwentaryzacja Bram i Grodzi (lista urządzeń w obiekcie)
        // Podobnie jak PWP, mamy strukturę systemową.
        // Ale użytkownik pisze: "W obiekcie może występować kilka systemów bram"
        // "System składa się z jednej lub więcej bram Oraz centralki sterującej"
        // To sugeruje, że Centrala jest "rodzicem" dla Bram, lub są na równi w grupie.
        // Najwygodniej będzie użyć modelu płaskiego z 'type' (gate/central) i 'system_number' do grupowania.

        // Pola dla bramy:
        // - type: grawitacyjna / elektryczna
        // - manufacturer
        // - fire_resistance_class (odporność ogniowa)

        // Pola dla centrali:
        // - manufacturer
        // - model

        // Wspólne:
        // - location
        // - system_number

        Schema::create('fire_gate_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained('client_objects')->onDelete('cascade');

            $table->string('type'); // 'gate' (Brama), 'central' (Centrala)
            $table->integer('system_number')->default(1);
            $table->string('location')->nullable();

            // Specyficzne dla bramy
            $table->string('gate_type')->nullable(); // 'gravitational' (Grawitacyjna), 'electric' (Elektryczna)
            $table->string('fire_resistance_class')->nullable(); // np. EI30, EI60

            // Specyficzne dla centrali i bramy (producent)
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable(); // głównie dla centrali

            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Pozycje w protokole
        Schema::create('protocol_fire_gate_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained('protocols')->onDelete('cascade');
            $table->foreignId('fire_gate_device_id')->nullable()->constrained('fire_gate_devices', 'id', 'proto_fg_dev_fk')->onDelete('set null');

            // Snapshot danych
            $table->string('type');
            $table->integer('system_number')->default(1);
            $table->string('location')->nullable();
            $table->string('gate_type')->nullable();
            $table->string('fire_resistance_class')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('model')->nullable();

            // Pomiary / Sprawdzenia
            // Dla bramy: zadziałał prawidłowo (Sprawny/niesprawny)
            // Dla centrali: akumulator (data), czujki, przyciski, sygnalizatory, silnik (jeśli dotyczy), ocena ogólna

            // Pola checkboxowe (dla centrali)
            $table->boolean('check_detectors')->nullable(); // Czujki
            $table->boolean('check_buttons')->nullable(); // Przyciski
            $table->boolean('check_signalers')->nullable(); // Sygnalizatory
            $table->boolean('check_holding_mechanism')->nullable(); // Mechanizm trzymający
            $table->boolean('check_drive')->nullable(); // Silnik napędowy (elektryczne)

            // Inne pola
            $table->string('battery_date')->nullable(); // Data akumulatorów (dla centrali)

            // Wynik i uwagi
            $table->enum('result', ['positive', 'negative'])->default('positive');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_fire_gate_devices');
        Schema::dropIfExists('fire_gate_devices');
    }
};
