<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Dodaj system Detekcja Gazów
        $systemId = null;
        if (DB::table('systems')->where('slug', 'detekcja-gazow')->doesntExist()) {
            $systemId = DB::table('systems')->insertGetId([
                'name' => 'Detekcja Gazów',
                'slug' => 'detekcja-gazow',
                'prefix' => 'DET',
                'has_periodic_review' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('protocol_templates')->insert([
                'system_id' => $systemId,
                'name' => 'Domyślny',
                'title' => 'PROTOKÓŁ PRZEGLĄDU SYSTEMU DETEKCJI GAZÓW',
                'description' => '<p>Przegląd systemu detekcji gazów obejmujący centrale, detektory oraz urządzenia sterujące.</p>',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 2. Tabele Centrale
        Schema::create('gas_detection_centrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained()->onDelete('cascade');
            $table->string('name')->comment('Nazwa/Model centrali');
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('protocol_gas_detection_centrals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained()->onDelete('cascade');
            $table->foreignId('gas_detection_central_id')->nullable();
            // Skrócona nazwa klucza obcego
            $table->foreign('gas_detection_central_id', 'pgdc_gdc_id_fk')->references('id')->on('gas_detection_centrals')->onDelete('set null');

            $table->string('name');
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0);

            $table->enum('result', ['positive', 'negative'])->default('positive');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 3. Tabele Detektory
        Schema::create('gas_detection_detectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained()->onDelete('cascade');
            $table->string('name')->comment('Nazwa/Typ detektora');
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('protocol_gas_detection_detectors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained()->onDelete('cascade');
            $table->foreignId('gas_detection_detector_id')->nullable();
            $table->foreign('gas_detection_detector_id', 'pgdd_gdd_id_fk')->references('id')->on('gas_detection_detectors')->onDelete('set null');

            $table->string('name');
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0);

            $table->enum('result', ['positive', 'negative', 'calibration'])->default('positive');
            $table->date('next_calibration_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Tabele Urządzenia Sterujące
        Schema::create('gas_detection_control_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained()->onDelete('cascade');
            $table->string('type')->comment('Wentylacja, Zawór MAG, Sygnalizator, Lampa');
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('protocol_gas_detection_control_devices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained()->onDelete('cascade');
            $table->foreignId('gas_detection_control_device_id')->nullable();
            $table->foreign('gas_detection_control_device_id', 'pgdcd_gdcd_id_fk')->references('id')->on('gas_detection_control_devices')->onDelete('set null');

            $table->string('type');
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0);

            $table->enum('result', ['positive', 'negative'])->default('positive');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocol_gas_detection_control_devices');
        Schema::dropIfExists('gas_detection_control_devices');
        Schema::dropIfExists('protocol_gas_detection_detectors');
        Schema::dropIfExists('gas_detection_detectors');
        Schema::dropIfExists('protocol_gas_detection_centrals');
        Schema::dropIfExists('gas_detection_centrals');
    }
};
