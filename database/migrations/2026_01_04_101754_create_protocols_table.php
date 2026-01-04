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
        Schema::create('protocols', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained('client_objects')->onDelete('cascade');
            $table->foreignId('system_id')->constrained()->onDelete('cascade');
            $table->string('number'); // Numer protokołu
            $table->date('date'); // Data protokołu
            $table->date('next_date')->nullable(); // Data następnego przeglądu
            $table->json('data')->nullable(); // Dane szczegółowe protokołu (elastyczne dla różnych systemów)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocols');
    }
};
