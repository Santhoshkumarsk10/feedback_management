<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Shift A',
                'code' => 'SHIFT-A',
                'start_time' => '06:00:00',
                'end_time' => '14:00:00',
                'description' => 'Morning shift operations & customer plant visitor tours',
                'is_active' => true,
            ],
            [
                'name' => 'Shift B',
                'code' => 'SHIFT-B',
                'start_time' => '14:00:00',
                'end_time' => '22:00:00',
                'description' => 'Afternoon & evening manufacturing and assembly line shift',
                'is_active' => true,
            ],
            [
                'name' => 'Shift C',
                'code' => 'SHIFT-C',
                'start_time' => '22:00:00',
                'end_time' => '06:00:00',
                'description' => 'Night operational shift and overnight maintenance monitoring',
                'is_active' => true,
            ],
        ];

        foreach ($shifts as $data) {
            Shift::updateOrCreate(
                ['code' => $data['code']],
                $data
            );
        }
    }
}
