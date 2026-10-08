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
        Schema::table('visits', function (Blueprint $table) {
            if (!Schema::hasColumn('visits', 'in_time')) {
                $table->dateTime('in_time')->nullable()->after('visit_date')->index();
            }
            if (!Schema::hasColumn('visits', 'out_time')) {
                $table->dateTime('out_time')->nullable()->after('in_time')->index();
            }
            if (!Schema::hasColumn('visits', 'department')) {
                $table->string('department', 100)->nullable()->after('purpose');
            }
        });

        Schema::table('feedbacks', function (Blueprint $table) {
            if (!Schema::hasColumn('feedbacks', 'submitted_mode')) {
                $table->enum('submitted_mode', ['direct', 'staff_assisted'])->default('direct')->after('comments')->index();
            }
            if (!Schema::hasColumn('feedbacks', 'submitted_by_staff_id')) {
                $table->foreignId('submitted_by_staff_id')->nullable()->after('submitted_mode')->constrained('users')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedbacks', function (Blueprint $table) {
            if (Schema::hasColumn('feedbacks', 'submitted_by_staff_id')) {
                $table->dropForeign(['submitted_by_staff_id']);
                $table->dropColumn('submitted_by_staff_id');
            }
            if (Schema::hasColumn('feedbacks', 'submitted_mode')) {
                $table->dropColumn('submitted_mode');
            }
        });

        Schema::table('visits', function (Blueprint $table) {
            if (Schema::hasColumn('visits', 'department')) {
                $table->dropColumn('department');
            }
            if (Schema::hasColumn('visits', 'out_time')) {
                $table->dropColumn('out_time');
            }
            if (Schema::hasColumn('visits', 'in_time')) {
                $table->dropColumn('in_time');
            }
        });
    }
};
