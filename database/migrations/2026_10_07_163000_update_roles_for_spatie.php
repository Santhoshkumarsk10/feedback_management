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
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'display_name')) {
                $table->string('display_name')->nullable()->after('name');
            }
        });

        // Sync display_name and normalize name to slug for Spatie compatibility
        $roles = DB::table('roles')->get();
        foreach ($roles as $r) {
            $displayName = $r->display_name ?: $r->name;
            $spatieName = $r->slug ?: strtolower(str_replace(' ', '_', $r->name));
            DB::table('roles')->where('id', $r->id)->update([
                'display_name' => $displayName,
                'name' => $spatieName,
                'guard_name' => 'web',
            ]);
        }

        // Ensure the 4 primary roles exist
        $primaryRoles = [
            [
                'name' => 'superadmin',
                'slug' => 'superadmin',
                'display_name' => 'Super Admin',
                'guard_name' => 'web',
                'description' => 'Full administrative access across all plants and system configurations',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'admin',
                'slug' => 'admin',
                'display_name' => 'Plant Admin',
                'guard_name' => 'web',
                'description' => 'Plant administrator managing users, facility tours, shifts, and operations',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'supervisor',
                'slug' => 'supervisor',
                'display_name' => 'Operations Supervisor',
                'guard_name' => 'web',
                'description' => 'Supervises staff, audits plant visits, feedback logs, and performance analytics',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'staff',
                'slug' => 'staff',
                'display_name' => 'Shift Staff',
                'guard_name' => 'web',
                'description' => 'Shift staff employee assisting plant visitors and capturing customer feedback',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'organizer',
                'slug' => 'organizer',
                'display_name' => 'Tour Organizer',
                'guard_name' => 'web',
                'description' => 'Legacy tour organizer role mapped to shift staff',
                'is_system' => true,
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        foreach ($primaryRoles as $pRole) {
            $exists = DB::table('roles')->where('name', $pRole['name'])->first();
            if (!$exists) {
                DB::table('roles')->insert($pRole);
            } else {
                DB::table('roles')->where('name', $pRole['name'])->update([
                    'display_name' => $pRole['display_name'],
                    'slug' => $pRole['slug'],
                    'guard_name' => 'web',
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'display_name')) {
                $table->dropColumn('display_name');
            }
        });
    }
};
