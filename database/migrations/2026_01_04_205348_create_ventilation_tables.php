<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Dodaj system Wentylacja jeśli nie istnieje
        $systemId = null;
        if (DB::table('systems')->where('slug', 'wentylacja')->doesntExist()) {
            $systemId = DB::table('systems')->insertGetId([
                'name' => 'Wentylacja',
                'slug' => 'wentylacja',
                'prefix' => 'WENT',
                'has_periodic_review' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Dodaj domyślny szablon protokołu dla Wentylacji
            DB::table('protocol_templates')->insert([
                'system_id' => $systemId,
                'name' => 'Domyślny',
                'title' => 'PROTOKÓŁ PRZEGLĄDU SYSTEMU WENTYLACJI',
                'description' => '<p>Przegląd systemu wentylacji obejmujący rozdzielnice oraz wentylatory.</p>',
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Tabela inwentaryzacji Rozdzielnic
        Schema::create('ventilation_distributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained()->onDelete('cascade');
            $table->string('name')->comment('Oznaczenie/Numer rozdzielnicy');
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabela protokołu Rozdzielnic
        Schema::create('protocol_ventilation_distributors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained()->onDelete('cascade');

            // Skrócona nazwa klucza obcego
            $table->foreignId('ventilation_distributor_id')->nullable();
            $table->foreign('ventilation_distributor_id', 'pvd_vd_id_fk')->references('id')->on('ventilation_distributors')->onDelete('set null');

            $table->string('name');
            $table->string('location')->nullable();

            // 1. Ocena wizualna
            $table->enum('check_visual_status', ['positive', 'negative', 'not_applicable'])->default('positive');
            $table->text('check_visual_notes')->nullable();

            // 2. Przewody i zaciski przyłączeniowe
            $table->enum('check_cables_status', ['positive', 'negative', 'not_applicable'])->default('positive');
            $table->text('check_cables_notes')->nullable();

            // 3. Urządzenia wewnątrz
            $table->enum('check_devices_status', ['positive', 'negative', 'not_applicable'])->default('positive');
            $table->text('check_devices_notes')->nullable();

            // 4. Przewody wewnątrz
            $table->enum('check_internal_cables_status', ['positive', 'negative', 'not_applicable'])->default('positive');
            $table->text('check_internal_cables_notes')->nullable();

            // 5. Wyłącznik główny
            $table->enum('check_main_switch_status', ['positive', 'negative', 'not_applicable'])->default('positive');
            $table->text('check_main_switch_notes')->nullable();

            // 6. Dokumentacja (Jest/Brak)
            $table->boolean('check_documentation_status')->default(true); // true = Jest, false = Brak
            $table->text('check_documentation_notes')->nullable();

            // 7. Wysterowania ręczne
            $table->enum('check_manual_controls_status', ['positive', 'negative', 'not_applicable'])->default('positive');
            $table->text('check_manual_controls_notes')->nullable();

            // 8. Sygnalizacja optyczna
            $table->enum('check_optical_status', ['positive', 'negative', 'not_applicable'])->default('positive');
            $table->text('check_optical_notes')->nullable();

            // 9. Sygnały wejściowe
            $table->enum('check_input_signals_status', ['positive', 'negative', 'not_applicable'])->default('positive');
            $table->text('check_input_signals_notes')->nullable();

            $table->timestamps();
        });

        // Tabela inwentaryzacji Wentylatorów
        Schema::create('ventilation_fans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_object_id')->constrained()->onDelete('cascade');
            $table->string('name')->comment('Typ lub nr urządzenia');
            $table->string('location')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Tabela protokołu Wentylatorów
        Schema::create('protocol_ventilation_fans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('protocol_id')->constrained()->onDelete('cascade');

            // Skrócona nazwa klucza obcego
            $table->foreignId('ventilation_fan_id')->nullable();
            $table->foreign('ventilation_fan_id', 'pvf_vf_id_fk')->references('id')->on('ventilation_fans')->onDelete('set null');

            $table->string('name');
            $table->string('location')->nullable();

            // Załączenie w alarmie II st. (TAK/NIE)
            $table->boolean('check_alarm_level_2')->default(true);

            // Stan techniczny (Poprawny/Uszkodzony)
            $table->enum('check_technical_condition', ['good', 'bad'])->default('good');

            // Stan przewodów i przyłączy (Poprawny/Uszkodzony)
            $table->enum('check_cables_condition', ['good', 'bad'])->default('good');

            // Pobór prądu I bieg (wartość lub "W normie")
            $table->string('current_1')->nullable();

            // Pobór prądu II bieg (wartość lub "W normie")
            $table->string('current_2')->nullable();

            // Ocena sprawności (Sprawne/Niesprawne)
            $table->enum('result', ['positive', 'negative'])->default('positive');

            // Uwagi
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_ventilation_fans');
        Schema::dropIfExists('ventilation_fans');
        Schema::dropIfExists('protocol_ventilation_distributors');
        Schema::dropIfExists('ventilation_distributors');

        // Nie usuwamy systemu z tabeli systems, aby nie psuć spójności danych historycznych
    }
};
