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
        // 1. Tabela słownikowa klas odporności
        Schema::create('door_resistance_classes', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // np. EI30, EI60
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Modyfikacja tabeli inwentaryzacji (doors)
        Schema::table('doors', function (Blueprint $table) {
            $table->foreignId('resistance_class_id')->nullable()->after('client_object_id')->constrained('door_resistance_classes')->onDelete('set null');
            $table->string('custom_resistance_class')->nullable()->after('resistance_class_id');
        });

        // 3. Migracja danych: Przeniesienie istniejących stringów do słownika
        // Pobieramy unikalne klasy, które nie są puste
        $existingClasses = DB::table('doors')
            ->whereNotNull('resistance_class')
            ->where('resistance_class', '!=', '')
            ->distinct()
            ->pluck('resistance_class');

        foreach ($existingClasses as $className) {
            // Sprawdź czy już istnieje w słowniku (może być duplikat jeśli uruchamiamy ponownie)
            $classId = DB::table('door_resistance_classes')->where('name', $className)->value('id');

            if (!$classId) {
                $classId = DB::table('door_resistance_classes')->insertGetId([
                    'name' => $className,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Zaktualizuj rekordy w doors
            DB::table('doors')
                ->where('resistance_class', $className)
                ->update(['resistance_class_id' => $classId]);
        }

        // 4. Usunięcie starej kolumny (po migracji)
        Schema::table('doors', function (Blueprint $table) {
            $table->dropColumn('resistance_class');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Przywrócenie kolumny
        Schema::table('doors', function (Blueprint $table) {
            $table->string('resistance_class')->nullable()->after('client_object_id');
        });

        // Przywrócenie danych
        $doors = DB::table('doors')->get();
        foreach ($doors as $door) {
            $name = null;
            if ($door->resistance_class_id) {
                $name = DB::table('door_resistance_classes')->where('id', $door->resistance_class_id)->value('name');
            } else {
                $name = $door->custom_resistance_class;
            }

            DB::table('doors')->where('id', $door->id)->update(['resistance_class' => $name]);
        }

        // Usunięcie nowych kolumn i tabeli
        Schema::table('doors', function (Blueprint $table) {
            $table->dropForeign(['resistance_class_id']);
            $table->dropColumn(['resistance_class_id', 'custom_resistance_class']);
        });

        Schema::dropIfExists('door_resistance_classes');
    }
};
