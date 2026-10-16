<?php

namespace Database\Seeders;

use App\Models\Plant;
use Illuminate\Database\Seeder;

class MasterSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Plants Master
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

        // 2. Roles, Permissions & User Accounts
        $this->call(RoleAndPermissionSeeder::class);
    }
}
