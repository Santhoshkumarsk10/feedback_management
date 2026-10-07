<?php

use App\Models\Visitor;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Create dedicated visitors master table
        if (!Schema::hasTable('visitors')) {
            Schema::create('visitors', function (Blueprint $table) {
                $table->id();
                $table->string('visitor_id', 60)->unique()->index();
                $table->string('name', 150);
                $table->string('company', 150)->nullable();
                $table->string('designation', 150)->nullable();
                $table->string('mobile', 20)->nullable()->index();
                $table->string('email', 150)->nullable();
                $table->string('external_source', 100)->default('third_party_api');
                $table->string('external_id', 100)->nullable()->index();
                $table->json('external_metadata')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Add visitor_id & visitor_code to visits table, and make organizer_id nullable
        Schema::table('visits', function (Blueprint $table) {
            if (!Schema::hasColumn('visits', 'visitor_id')) {
                $table->foreignId('visitor_id')->nullable()->after('id')->constrained('visitors')->nullOnDelete();
            }
            if (!Schema::hasColumn('visits', 'visitor_code')) {
                $table->string('visitor_code', 60)->nullable()->after('visitor_id')->index();
            }
        });

        // Make organizer_id nullable if not already
        DB::statement('ALTER TABLE visits MODIFY organizer_id BIGINT UNSIGNED NULL');

        // 3. Backfill existing visits into visitors table
        $existingVisits = DB::table('visits')->orderBy('id')->get();
        $counter = 1;

        foreach ($existingVisits as $visit) {
            $year = $visit->visit_date ? date('Y', strtotime($visit->visit_date)) : date('Y');
            $generatedCode = sprintf('VIS-%s-%04d', $year, $counter++);

            // Check if visitor already exists with same phone or name/company
            $visitorQuery = DB::table('visitors');
            if (!empty($visit->visitor_mobile)) {
                $visitor = $visitorQuery->where('mobile', $visit->visitor_mobile)->first();
            } else {
                $visitor = $visitorQuery->where('name', $visit->visitor_name)
                    ->where('company', $visit->visitor_company)
                    ->first();
            }

            if (!$visitor) {
                $visitorId = DB::table('visitors')->insertGetId([
                    'visitor_id' => $generatedCode,
                    'name' => $visit->visitor_name,
                    'company' => $visit->visitor_company,
                    'designation' => $visit->visitor_designation ?? null,
                    'mobile' => $visit->visitor_mobile ?? null,
                    'email' => $visit->visitor_email ?? null,
                    'external_source' => 'legacy_visit',
                    'is_active' => true,
                    'created_at' => $visit->created_at ?? now(),
                    'updated_at' => $visit->updated_at ?? now(),
                ]);
                $finalCode = $generatedCode;
            } else {
                $visitorId = $visitor->id;
                $finalCode = $visitor->visitor_id;
            }

            DB::table('visits')->where('id', $visit->id)->update([
                'visitor_id' => $visitorId,
                'visitor_code' => $finalCode,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            if (Schema::hasColumn('visits', 'visitor_id')) {
                $table->dropForeign(['visitor_id']);
                $table->dropColumn('visitor_id');
            }
            if (Schema::hasColumn('visits', 'visitor_code')) {
                $table->dropColumn('visitor_code');
            }
        });

        Schema::dropIfExists('visitors');
    }
};
