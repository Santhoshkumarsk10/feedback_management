<?php

namespace Database\Seeders;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Seeder;

class AuditLogSeeder extends Seeder
{
    public function run(): void
    {
        $superadmin = User::where('role', 'superadmin')->first();
        $admin = User::where('role', 'admin')->first();

        $events = [
            [
                'user_id' => $superadmin?->id,
                'user_name' => $superadmin?->name ?? 'Super Admin',
                'user_role' => 'superadmin',
                'action' => 'login',
                'module' => 'auth',
                'description' => 'Administrator successfully authenticated into operations portal.',
                'details' => ['method' => 'web_credentials', 'browser' => 'Chrome / Linux'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 Chrome/134.0.0.0 Safari/537.36',
                'created_at' => now()->subHours(8),
            ],
            [
                'user_id' => $superadmin?->id,
                'user_name' => $superadmin?->name ?? 'Super Admin',
                'user_role' => 'superadmin',
                'action' => 'create',
                'module' => 'plants',
                'description' => 'Registered new plant facility: PLANT-01 — Machine Tools Division',
                'details' => ['code' => 'PLANT-01', 'location' => 'Chembarambakkam, Chennai'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subHours(7)->subMinutes(45),
            ],
            [
                'user_id' => $superadmin?->id,
                'user_name' => $superadmin?->name ?? 'Super Admin',
                'user_role' => 'superadmin',
                'action' => 'create',
                'module' => 'plants',
                'description' => 'Registered new plant facility: PLANT-02 — Injection Molding Facility',
                'details' => ['code' => 'PLANT-02', 'location' => 'Chembarambakkam, Chennai'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subHours(7)->subMinutes(30),
            ],
            [
                'user_id' => $superadmin?->id,
                'user_name' => $superadmin?->name ?? 'Super Admin',
                'user_role' => 'superadmin',
                'action' => 'create',
                'module' => 'plants',
                'description' => 'Registered new plant facility: PLANT-03 — Die Casting & Heavy Machinery',
                'details' => ['code' => 'PLANT-03', 'location' => 'Sriperumbudur, Tamil Nadu'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subHours(7)->subMinutes(15),
            ],
            [
                'user_id' => $superadmin?->id,
                'user_name' => $superadmin?->name ?? 'Super Admin',
                'user_role' => 'superadmin',
                'action' => 'create',
                'module' => 'roles',
                'description' => 'Configured operational role: Quality Supervisor (quality_supervisor)',
                'details' => ['slug' => 'quality_supervisor', 'is_system' => false],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subHours(6),
            ],
            [
                'user_id' => $superadmin?->id,
                'user_name' => $superadmin?->name ?? 'Super Admin',
                'user_role' => 'superadmin',
                'action' => 'create',
                'module' => 'users',
                'description' => 'Registered user account: Ravi Kumar (organizer) at PLANT-02',
                'details' => ['email' => 'ravi@plant.test', 'plant_code' => 'PLANT-02', 'role' => 'organizer'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subHours(5)->subMinutes(30),
            ],
            [
                'user_id' => $superadmin?->id,
                'user_name' => $superadmin?->name ?? 'Super Admin',
                'user_role' => 'superadmin',
                'action' => 'create',
                'module' => 'users',
                'description' => 'Registered user account: Priya S (organizer) at PLANT-02',
                'details' => ['email' => 'priya@plant.test', 'plant_code' => 'PLANT-02', 'role' => 'organizer'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subHours(5)->subMinutes(15),
            ],
            [
                'user_id' => $superadmin?->id,
                'user_name' => $superadmin?->name ?? 'Super Admin',
                'user_role' => 'superadmin',
                'action' => 'update',
                'module' => 'questions',
                'description' => 'Configured Shibaura Technical Centre 23 survey evaluation questions.',
                'details' => ['sections_count' => 4, 'total_questions' => 23],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subHours(4),
            ],
            [
                'user_id' => $admin?->id,
                'user_name' => $admin?->name ?? 'Plant Admin',
                'user_role' => 'admin',
                'action' => 'login',
                'module' => 'auth',
                'description' => 'Plant Supervisor authenticated session.',
                'details' => ['method' => 'web_credentials'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subHours(3),
            ],
            [
                'user_id' => $admin?->id,
                'user_name' => $admin?->name ?? 'Plant Admin',
                'user_role' => 'admin',
                'action' => 'create',
                'module' => 'visits',
                'description' => 'Recorded plant visit registration for Tata Motors delegation.',
                'details' => ['visitor' => 'Rajesh Sharma', 'company' => 'Tata Motors Ltd', 'plant_id' => 1],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subHours(2),
            ],
            [
                'user_id' => $admin?->id,
                'user_name' => $admin?->name ?? 'Plant Admin',
                'user_role' => 'admin',
                'action' => 'create',
                'module' => 'feedbacks',
                'description' => 'Customer feedback survey recorded with 5-Star overall rating.',
                'details' => ['rating' => 5, 'nps_score' => 10, 'company' => 'Tata Motors Ltd'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subHours(1)->subMinutes(30),
            ],
            [
                'user_id' => $admin?->id,
                'user_name' => $admin?->name ?? 'Plant Admin',
                'user_role' => 'admin',
                'action' => 'export',
                'module' => 'reports',
                'description' => 'Exported monthly organizer evaluation spreadsheet report.',
                'details' => ['format' => 'csv', 'period' => 'this_month'],
                'ip_address' => '127.0.0.1',
                'user_agent' => 'Mozilla/5.0 (X11; Linux x86_64)',
                'created_at' => now()->subMinutes(25),
            ],
        ];

        foreach ($events as $e) {
            AuditLog::create($e);
        }
    }
}
