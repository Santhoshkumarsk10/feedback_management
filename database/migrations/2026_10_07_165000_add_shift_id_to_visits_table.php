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
        Schema::table('visits', function (Blueprint $table) {
            $table->foreignId('shift_id')
                ->nullable()
                ->after('organizer_id')
                ->constrained('shifts')
                ->nullOnDelete();

            $table->index(['shift_id', 'visit_date']);
        });

        // Distribute existing visits across shifts A, B, C for realistic data
        $shiftA = DB::table('shifts')->where('code', 'SHIFT-A')->value('id');
        $shiftB = DB::table('shifts')->where('code', 'SHIFT-B')->value('id');
        $shiftC = DB::table('shifts')->where('code', 'SHIFT-C')->value('id');

        if ($shiftA) {
            $visits = DB::table('visits')->get();
            foreach ($visits as $index => $visit) {
                // Distribute visits across shifts: 60% Shift A, 30% Shift B, 10% Shift C
                $assignedShift = ($index % 10 < 6) ? $shiftA : (($index % 10 < 9) ? ($shiftB ?: $shiftA) : ($shiftC ?: $shiftA));
                DB::table('visits')->where('id', $visit->id)->update(['shift_id' => $assignedShift]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropForeign(['shift_id']);
            $table->dropIndex(['shift_id', 'visit_date']);
            $table->dropColumn('shift_id');
        });
    }
};
