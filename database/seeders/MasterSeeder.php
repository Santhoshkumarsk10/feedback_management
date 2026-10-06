<?php

namespace Database\Seeders;

use App\Models\Plant;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Roles Master
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'superadmin',
                'description' => 'Full administrative access to all plants, master settings, questionnaires, and user security',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Plant Admin',
                'slug' => 'admin',
                'description' => 'Plant-level supervisor managing local visitors, tour organizers, and feedback analytics',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Tour Organizer',
                'slug' => 'organizer',
                'description' => 'SMI plant engineer escorting visitors and collecting customer evaluation reviews',
                'is_system' => true,
                'is_active' => true,
            ],
            [
                'name' => 'Quality Supervisor',
                'slug' => 'quality_supervisor',
                'description' => 'Audits customer satisfaction scores, NPS ratings, and qualitative visit feedback',
                'is_system' => false,
                'is_active' => true,
            ],
        ];

        foreach ($roles as $r) {
            Role::updateOrCreate(['slug' => $r['slug']], $r);
        }

        // 2. Plants Master
        $plants = [
            [
                'name' => 'Plant 1 — Machine Tools Division',
                'code' => 'PLANT-01',
                'location' => 'Chembarambakkam, Chennai, Tamil Nadu',
                'contact_email' => 'plant1.ops@shibaura-machine.in',
                'contact_phone' => '9844268121',
                'description' => 'CNC Horizontal Boring Machines, Machining Centers, and Precision Machine Tools manufacturing',
                'is_active' => true,
            ],
            [
                'name' => 'Plant 2 — Injection Molding Facility',
                'code' => 'PLANT-02',
                'location' => 'Chembarambakkam, Chennai, Tamil Nadu',
                'contact_email' => 'plant2.imm@shibaura-machine.in',
                'contact_phone' => '9844268122',
                'description' => 'All-Electric and Hydraulic Injection Molding Machines, Customer Mold Trials, and Demo Cell',
                'is_active' => true,
            ],
            [
                'name' => 'Plant 3 — Die Casting & Heavy Machinery',
                'code' => 'PLANT-03',
                'location' => 'Sriperumbudur Industrial Corridor, Tamil Nadu',
                'contact_email' => 'plant3.dc@shibaura-machine.in',
                'contact_phone' => '9844268123',
                'description' => 'High Pressure Die Casting Machines, Heavy Foundry, and Extrusion Machinery lines',
                'is_active' => true,
            ],
            [
                'name' => 'Plant 4 — Shibaura Technical Centre',
                'code' => 'PLANT-04',
                'location' => 'Tech Centre & Customer Experience Zone, Chennai',
                'contact_email' => 'techcentre@shibaura-machine.in',
                'contact_phone' => '9844268124',
                'description' => 'IoT machiNETCloud Demo Suite, Virtual Reality (VR) Zone, and Customer Training Academy',
                'is_active' => true,
            ],
        ];

        foreach ($plants as $p) {
            Plant::updateOrCreate(['code' => $p['code']], $p);
        }

        // 3. Link existing Users to Roles and Plants
        $p1 = Plant::where('code', 'PLANT-01')->first();
        $p2 = Plant::where('code', 'PLANT-02')->first();
        $p3 = Plant::where('code', 'PLANT-03')->first();
        $p4 = Plant::where('code', 'PLANT-04')->first();

        $roleSuper = Role::where('slug', 'superadmin')->first();
        $roleAdmin = Role::where('slug', 'admin')->first();
        $roleOrganizer = Role::where('slug', 'organizer')->first();

        // Superadmin & Admin
        User::where('email', 'superadmin@plant.test')->update([
            'role_id' => $roleSuper?->id,
            'plant_id' => $p4?->id,
        ]);

        User::where('email', 'admin@plant.test')->update([
            'role_id' => $roleAdmin?->id,
            'plant_id' => $p1?->id,
        ]);

        // Organizers
        User::where('email', 'ravi@plant.test')->update([
            'role_id' => $roleOrganizer?->id,
            'plant_id' => $p2?->id,
        ]);

        User::where('email', 'priya@plant.test')->update([
            'role_id' => $roleOrganizer?->id,
            'plant_id' => $p2?->id,
        ]);

        User::where('email', 'karthik@plant.test')->update([
            'role_id' => $roleOrganizer?->id,
            'plant_id' => $p1?->id,
        ]);

        User::where('email', 'suresh@plant.test')->update([
            'role_id' => $roleOrganizer?->id,
            'plant_id' => $p3?->id,
        ]);

        // Default any remaining users
        User::whereNull('role_id')->where('role', 'organizer')->update([
            'role_id' => $roleOrganizer?->id,
            'plant_id' => $p1?->id,
        ]);
    }
}
