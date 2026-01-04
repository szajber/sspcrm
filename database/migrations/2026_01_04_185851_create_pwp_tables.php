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
        // 1. Inwentaryzacja PWP (lista urządzeń w obiekcie)
        // System składa się z wyłącznika (Switch) i punktów aktywacji (Triggers)
        // Możemy to zamodelować jako dwie tabele lub jedną elastyczną.
        // Ponieważ użytkownik mówi o "systemach PWP", z których każdy ma 1+ wyłączników i 1+ wyzwalaczy.
        // Ale najprościej będzie trzymać listę elementów z typem: 'switch' (wyłącznik) lub 'trigger' (wyzwalacz).
        // I grupować je po 'pwp_system_id' jeśli chcemy grupować w systemy.
        // Użytkownik napisał: "System składa się z jednego lub więcej wyłączników... Oraz punktów aktywacji".
        // Więc zróbmy strukturę hierarchiczną:
        // PwpSystem (główny obiekt, np. "PWP 1") -> PwpDevice (urządzenia: wyłącznik, wyzwalacz)

        // Ale w kroku 2 zazwyczaj dodajemy urządzenia na płaskiej liście.
        // Spróbujmy modelu: PwpDevice z polem 'type' (switch/trigger) i opcjonalnym 'system_name' lub 'group_id' do grupowania.
        // Jednak prośba mówi: "W obiekcie może wystepwac kilka systemów PWP".
        // Więc zróbmy tabelę PwpDevice, która ma:
        // - type: 'switch' (wyłącznik) lub 'trigger' (punkt aktywacji)
        // - location: np. "rozdzielnia główna" lub "Wejście do klatki"
        // - system_number: numer systemu (np. 1, 2) do grupowania elementów w jeden system PWP.

        Schema::create('pwp_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained('client_objects')->onDelete('cascade');
            $table->string('type'); // 'switch' (Wyłącznik), 'trigger' (Punkt aktywacji)
            $table->string('location')->nullable();
            $table->integer('system_number')->default(1); // Numer systemu PWP (grupowanie)

            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // 2. Pozycje w protokole
        Schema::create('protocol_pwp_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained('protocols')->onDelete('cascade');
            $table->foreignId('pwp_device_id')->nullable()->constrained('pwp_devices', 'id', 'proto_pwp_dev_fk')->onDelete('set null');

            // Snapshot danych
            $table->string('type');
            $table->string('location')->nullable();
            $table->integer('system_number')->default(1);

            // Pomiary / Sprawdzenia
            // Dla wyłącznika (switch): zadziałał prawidłowo (Sprawny/niesprawny)
            // Dla wyzwalacza (trigger): dostęp, oznakowanie, stan techniczny, zadziałanie (Sprawny/niesprawny)
            // Możemy użyć wspólnych pól boolean dla szczegółowych testów triggera.

            $table->boolean('check_access')->nullable(); // Dostęp (tylko trigger)
            $table->boolean('check_signage')->nullable(); // Oznakowanie (tylko trigger)
            $table->boolean('check_condition')->nullable(); // Stan techniczny (tylko trigger)
            $table->boolean('check_activation')->nullable(); // Zadziałanie (switch i trigger)

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
        Schema::dropIfExists('protocol_pwp_devices');
        Schema::dropIfExists('pwp_devices');
    }
};
