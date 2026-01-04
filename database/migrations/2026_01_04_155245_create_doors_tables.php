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
        // Tabela inwentaryzacji drzwi
        Schema::create('doors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained('client_objects')->onDelete('cascade');
            $table->string('resistance_class')->nullable(); // Klasa odporności
            $table->string('location')->nullable(); // Lokalizacja
            $table->integer('sort_order')->default(0);
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabela pozycji drzwi w protokole
        Schema::create('protocol_doors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained('protocols')->onDelete('cascade');
            $table->foreignId('door_id')->nullable()->constrained('doors')->onDelete('set null');
            $table->string('resistance_class')->nullable();
            $table->string('location')->nullable();
            $table->string('status')->default('sprawne'); // sprawne, niesprawne
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_doors');
        Schema::dropIfExists('doors');
    }
};
