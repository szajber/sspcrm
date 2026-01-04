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
        // Migracja danych
        $systems = DB::table('systems')->get();
        foreach ($systems as $system) {
            if ($system->protocol_title || $system->description) {
                DB::table('protocol_templates')->insert([
                    'system_id' => $system->id,
                    'name' => 'Domyślny',
                    'title' => $system->protocol_title,
                    'description' => $system->description,
                    'is_default' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        Schema::table('systems', function (Blueprint $table) {
            $table->dropColumn(['protocol_title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('systems', function (Blueprint $table) {
            $table->string('protocol_title')->nullable();
            $table->text('description')->nullable();
        });
    }
};
