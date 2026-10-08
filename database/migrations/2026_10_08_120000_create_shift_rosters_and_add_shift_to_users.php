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
        if (!Schema::hasColumn('users', 'shift_id')) {
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

                $table->unique(['user_id', 'roster_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shift_rosters');

        if (Schema::hasColumn('users', 'shift_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropForeign(['shift_id']);
                $table->dropColumn('shift_id');
            });
        }
    }
};
