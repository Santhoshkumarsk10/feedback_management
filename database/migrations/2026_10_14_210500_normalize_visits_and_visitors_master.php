<?php

use App\Models\Visitor;
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
        // 1. Backfill any visits that lack a visitor_id
        $unlinkedVisits = DB::table('visits')->whereNull('visitor_id')->orderBy('id')->get();

        foreach ($unlinkedVisits as $visit) {
            $mobile = !empty($visit->visitor_mobile) ? preg_replace('/[^0-9]/', '', (string)$visit->visitor_mobile) : null;
            $email = !empty($visit->visitor_email) ? strtolower(trim((string)$visit->visitor_email)) : null;
            $name = trim((string)($visit->visitor_name ?? 'Guest Visitor'));
            $company = trim((string)($visit->visitor_company ?? ''));

            $matchedVisitor = null;
            if (!empty($mobile)) {
                $matchedVisitor = DB::table('visitors')->where('mobile', $mobile)->first();
            }
            if (!$matchedVisitor && !empty($email)) {
                $matchedVisitor = DB::table('visitors')->where('email', $email)->first();
            }
            if (!$matchedVisitor && !empty($name) && !empty($company)) {
                $matchedVisitor = DB::table('visitors')->where('name', $name)->where('company', $company)->first();
            }

            if ($matchedVisitor) {
                $visitorId = $matchedVisitor->id;
                $visitorCode = $matchedVisitor->visitor_id;
            } else {
                $year = $visit->visit_date ? date('Y', strtotime($visit->visit_date)) : date('Y');
                $code = Visitor::generateUniqueVisitorId('VIS');

                $visitorId = DB::table('visitors')->insertGetId([
                    'visitor_id' => $code,
                    'name' => $name ?: 'Guest Visitor',
                    'company' => $company ?: null,
                    'designation' => $visit->visitor_designation ?? null,
                    'mobile' => $mobile ?: null,
                    'email' => $email ?: null,
                    'external_source' => 'legacy_visit',
                    'is_active' => true,
                    'created_at' => $visit->created_at ?? now(),
                    'updated_at' => $visit->updated_at ?? now(),
                ]);
                $visitorCode = $code;
            }

            DB::table('visits')->where('id', $visit->id)->update([
                'visitor_id' => $visitorId,
                'visitor_code' => $visitorCode,
            ]);
        }

        // 2. Drop the redundant duplicated visitor profile columns from visits table
        Schema::table('visits', function (Blueprint $table) {
            $columnsToDrop = [];
            foreach (['visitor_name', 'visitor_designation', 'visitor_mobile', 'visitor_company', 'visitor_email'] as $col) {
                if (Schema::hasColumn('visits', $col)) {
                    $columnsToDrop[] = $col;
                }
            }
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Re-add redundant columns if rolling back
        Schema::table('visits', function (Blueprint $table) {
            if (!Schema::hasColumn('visits', 'visitor_name')) {
                $table->string('visitor_name')->nullable()->after('shift_id');
            }
            if (!Schema::hasColumn('visits', 'visitor_designation')) {
                $table->string('visitor_designation', 150)->nullable()->after('visitor_name');
            }
            if (!Schema::hasColumn('visits', 'visitor_mobile')) {
                $table->string('visitor_mobile', 20)->nullable()->after('visitor_designation');
            }
            if (!Schema::hasColumn('visits', 'visitor_company')) {
                $table->string('visitor_company', 150)->nullable()->after('visitor_mobile');
            }
            if (!Schema::hasColumn('visits', 'visitor_email')) {
                $table->string('visitor_email', 150)->nullable()->after('visitor_company');
            }
        });

        // 2. Populate data back from visitors table
        $visits = DB::table('visits')->whereNotNull('visitor_id')->get();
        foreach ($visits as $visit) {
            $visitor = DB::table('visitors')->where('id', $visit->visitor_id)->first();
            if ($visitor) {
                DB::table('visits')->where('id', $visit->id)->update([
                    'visitor_name' => $visitor->name,
                    'visitor_designation' => $visitor->designation,
                    'visitor_mobile' => $visitor->mobile,
                    'visitor_company' => $visitor->company,
                    'visitor_email' => $visitor->email,
                ]);
            }
        }
    }
};
