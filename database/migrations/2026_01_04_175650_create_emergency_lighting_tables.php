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
        // 1. Inwentaryzacja oświetlenia (lista urządzeń w obiekcie)
        Schema::create('emergency_lighting_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained('client_objects')->onDelete('cascade');
            $table->string('type'); // Awaryjna, Ewakuacyjna, Awaryjna zewnętrzna
            $table->string('location')->nullable();

            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Pozycje w protokole
        Schema::create('protocol_emergency_lighting_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained('protocols')->onDelete('cascade');
            // Powiązanie z oryginałem (opcjonalne, ale przydatne)
            $table->foreignId('emergency_lighting_device_id')->nullable()->constrained('emergency_lighting_devices', 'id', 'proto_emerg_light_fk')->onDelete('set null');

            // Snapshot danych
            $table->string('type');
            $table->string('location')->nullable();

            // Pomiary / Sprawdzenia
            $table->boolean('check_startup_time')->default(false); // Czas uruchomienia poniżej 2 sek
            $table->boolean('check_duration')->default(false); // Czas świecenia powyżej 1h

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
        Schema::dropIfExists('protocol_emergency_lighting_devices');
        Schema::dropIfExists('emergency_lighting_devices');
    }
};
