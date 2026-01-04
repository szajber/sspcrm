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
        // 1. Typy gaśnic (słownik w ustawieniach)
        Schema::create('fire_extinguisher_types', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // np. GP-6x ABC, GS-5x B
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Gaśnice przypisane do obiektu (inwentaryzacja)
        Schema::create('fire_extinguishers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained('client_objects')->onDelete('cascade');
            $table->foreignId('type_id')->nullable()->constrained('fire_extinguisher_types')->onDelete('set null'); // Typ z listy
            $table->string('custom_type')->nullable(); // Lub typ wpisany ręcznie (jeśli nie wybrano z listy)
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0); // Pozycja na liście
            $table->boolean('is_active')->default(true); // Ukrywanie usuniętych
            $table->timestamps();
        });

        // 3. Stan gaśnic w konkretnym protokole
        Schema::create('protocol_fire_extinguishers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained('protocols')->onDelete('cascade');
            $table->foreignId('fire_extinguisher_id')->constrained('fire_extinguishers')->onDelete('cascade');

            // Kopia danych w momencie tworzenia protokołu (aby zmiany w inwentaryzacji nie psuły historii)
            $table->string('type_name')->nullable(); // Nazwa typu (z relacji lub custom)
            $table->string('location')->nullable();

            // Dane z przeglądu
            $table->enum('status', ['legalizacja', 'remont', 'zlom', 'brak', 'po_remoncie', 'nowa'])->default('legalizacja');
            $table->date('next_service_date')->nullable(); // Data następnego remontu
            $table->text('notes')->nullable(); // Uwagi

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_fire_extinguishers');
        Schema::dropIfExists('fire_extinguishers');
        Schema::dropIfExists('fire_extinguisher_types');
    }
};
