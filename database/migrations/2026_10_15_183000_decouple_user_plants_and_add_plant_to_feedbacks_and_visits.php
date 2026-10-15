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
        // 1. Remove plant_id from users table (users are no longer assigned to a plant)
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'plant_id')) {
            Schema::table('users', function (Blueprint $table) {
                // Drop foreign key if exists
                try {
                    $table->dropForeign(['plant_id']);
                } catch (\Throwable $e) {
                    // Foreign key might have a different name or be omitted in sqlite
                }
                $table->dropColumn('plant_id');
            });
        }

        // 2. Add plant_id to visits table
        if (Schema::hasTable('visits') && !Schema::hasColumn('visits', 'plant_id')) {
            Schema::table('visits', function (Blueprint $table) {
                $table->foreignId('plant_id')
                    ->nullable()
                    ->after('visitor_code')
                    ->constrained('plants')
                    ->nullOnDelete();
            });
        }

        // 3. Add plant_id to feedbacks table
        if (Schema::hasTable('feedbacks') && !Schema::hasColumn('feedbacks', 'plant_id')) {
            Schema::table('feedbacks', function (Blueprint $table) {
                $table->foreignId('plant_id')
                    ->nullable()
                    ->after('visit_id')
                    ->constrained('plants')
                    ->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('feedbacks') && Schema::hasColumn('feedbacks', 'plant_id')) {
            Schema::table('feedbacks', function (Blueprint $table) {
                try {
                    $table->dropForeign(['plant_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('plant_id');
            });
        }

        if (Schema::hasTable('visits') && Schema::hasColumn('visits', 'plant_id')) {
            Schema::table('visits', function (Blueprint $table) {
                try {
                    $table->dropForeign(['plant_id']);
                } catch (\Throwable $e) {}
                $table->dropColumn('plant_id');
            });
        }

        if (Schema::hasTable('users') && !Schema::hasColumn('users', 'plant_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('plant_id')
                    ->nullable()
                    ->after('password')
                    ->constrained('plants')
                    ->nullOnDelete();
            });
        }
    }
};
