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
        // 1. Systems: prefix, periodic review flag
        Schema::table('systems', function (Blueprint $table) {
            if (!Schema::hasColumn('systems', 'prefix')) {
                $table->string('prefix', 10)->nullable()->after('slug');
            }
            if (!Schema::hasColumn('systems', 'has_periodic_review')) {
                $table->boolean('has_periodic_review')->default(true)->after('prefix');
            }
        });

        // 2. Users: position (only if not exists, signature_path already exists)
        Schema::table('users', function (Blueprint $table) {
             if (!Schema::hasColumn('users', 'position')) {
                 $table->string('position')->nullable()->after('email');
             }
        });

        // 3. Protocols: performer_id, review_date, status, number_index
        Schema::table('protocols', function (Blueprint $table) {
            $table->foreignId('performer_id')->nullable()->after('system_id')->constrained('users')->onDelete('set null');
            $table->enum('status', ['draft', 'completed'])->default('draft')->after('data');
            $table->integer('number_index')->nullable()->after('number'); // For sequencing
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('protocols', function (Blueprint $table) {
            // Check foreign key existence before dropping?
            // Simplified for now, assuming standard rollback.
             if (Schema::hasColumn('protocols', 'performer_id')) {
                $table->dropForeign(['performer_id']);
                $table->dropColumn(['performer_id']);
             }
             if (Schema::hasColumn('protocols', 'status')) {
                $table->dropColumn(['status']);
             }
             if (Schema::hasColumn('protocols', 'number_index')) {
                $table->dropColumn(['number_index']);
             }
        });

        Schema::table('users', function (Blueprint $table) {
             if (Schema::hasColumn('users', 'position')) {
                $table->dropColumn(['position']);
             }
        });

        Schema::table('systems', function (Blueprint $table) {
             if (Schema::hasColumn('systems', 'prefix')) {
                $table->dropColumn(['prefix']);
             }
             if (Schema::hasColumn('systems', 'has_periodic_review')) {
                $table->dropColumn(['has_periodic_review']);
             }
        });
    }
};
