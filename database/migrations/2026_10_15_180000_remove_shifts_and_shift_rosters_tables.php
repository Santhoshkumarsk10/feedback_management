<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Drop foreign key and column from visits table
        if (Schema::hasTable('visits') && Schema::hasColumn('visits', 'shift_id')) {
            Schema::table('visits', function (Blueprint $table) {
                try {
                    $table->dropForeign(['shift_id']);
                } catch (\Throwable $e) {
                    // Ignore if foreign key constraint already removed
                }
                try {
                    $table->dropIndex(['shift_id', 'visit_date']);
                } catch (\Throwable $e) {
                    // Ignore if composite index doesn't exist
                }
                $table->dropColumn('shift_id');
            });
        }

        // 2. Drop foreign key and column from users table
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'shift_id')) {
            Schema::table('users', function (Blueprint $table) {
                try {
                    $table->dropForeign(['shift_id']);
                } catch (\Throwable $e) {
                    // Ignore if foreign key constraint already removed
                }
                $table->dropColumn('shift_id');
            });
        }

        // 3. Drop shift_rosters table
        Schema::dropIfExists('shift_rosters');

        // 4. Drop shifts table
        Schema::dropIfExists('shifts');

        // 5. Clean up manage-shifts permission if Spatie tables exist
        try {
            if (Schema::hasTable('permissions')) {
                $permId = DB::table('permissions')->where('name', 'manage-shifts')->value('id');
                if ($permId) {
                    if (Schema::hasTable('role_has_permissions')) {
                        DB::table('role_has_permissions')->where('permission_id', $permId)->delete();
                    }
                    if (Schema::hasTable('model_has_permissions')) {
                        DB::table('model_has_permissions')->where('permission_id', $permId)->delete();
                    }
                    DB::table('permissions')->where('id', $permId)->delete();
                }
            }
        } catch (\Throwable $e) {
            // Ignore if permissions tables are not present
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasTable('shifts')) {
            Schema::create('shifts', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->time('start_time');
                $table->time('end_time');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('visits') && !Schema::hasColumn('visits', 'shift_id')) {
            Schema::table('visits', function (Blueprint $table) {
                $table->foreignId('shift_id')->nullable()->after('organizer_id')->constrained('shifts')->nullOnDelete();
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'shift_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('shift_id')->nullable()->after('department')->constrained('shifts')->nullOnDelete();
            });
        }

        if (!Schema::hasTable('shift_rosters')) {
            Schema::create('shift_rosters', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
                $table->date('roster_date');
                $table->string('notes')->nullable();
                $table->timestamps();
            });
        }
    }
};
