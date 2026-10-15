<?php

namespace Database\Seeders;

use App\Models\Visit;
use App\Models\Visitor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DailyVisitsSeeder extends Seeder
{
    /**
     * Seed 10 realistic, unique, non-duplicate plant visitors and visits for today.
     * All visits start with organizer_id = null so visitors can choose their host on the app.
     */
    public function run(): void
    {
        $today = today()->toDateString();

        // 1. Remove duplicate/unrealistic test visits created today that have no real feedback
        $unwantedCodes = [
            'VIS-2026-0003', 'VIS-2026-0004', 'VIS-2026-0005', 'VIS-2026-0019',
            'VIS-2026-0020', 'VIS-2026-0021', 'VIS-2026-0022', 'VIS-2026-0024',
            'VIS-2026-0061', 'VIS-2026-0063',
        ];
        Visit::whereDate('visit_date', $today)
            ->where(function ($q) use ($unwantedCodes) {
                $q->whereIn('visitor_code', $unwantedCodes)
                  ->orWhere('visitor_code', 'EXT-PASS-8814')
                  ->orWhereHas('visitor', function ($vq) {
                      $vq->where('name', 'like', '%Test%')
                         ->orWhere('name', 'like', '%Candidate%')
                         ->orWhere('name', 'like', '%Demo%')
                         ->orWhere('name', 'like', '%Early%')
                         ->orWhere('name', 'like', '%Deepak Chopra%')
                         ->orWhere('name', 'like', '%Completed%');
                  });
            })
            ->doesntHave('feedback')
            ->delete();

        // 2. Exactly 10 Distinct, Realistic Industrial Visitors
        $visitorsData = [
            [
                'pass_code' => 'EXT-PASS-8812',
                'name' => 'Aravind Swaminathan',
                'company' => 'Lucas TVS Limited',
                'designation' => 'Plant Engineering Head',
                'mobile' => '9840123456',
                'email' => 'aravind.s@lucastvs.com',
                'purpose' => 'High-Speed Injection Moulding Machinery Trial & Demonstration',
                'department' => 'Injection Moulding Division',
                'in_time' => '09:30:00',
            ],
            [
                'pass_code' => 'EXT-PASS-8813',
                'name' => 'Dr. Meenakshi Sundaram',
                'company' => 'Motherson Sumi Systems',
                'designation' => 'Quality Assurance Director',
                'mobile' => '9840765432',
                'email' => 'meenakshi.sundaram@motherson.com',
                'purpose' => 'Die Casting Machinery Calibration & Precision Quality Audit',
                'department' => 'Die Casting Centre',
                'in_time' => '10:15:00',
            ],
            [
                'pass_code' => 'EXT-PASS-8814',
                'name' => 'Rajeshwar Natarajan',
                'company' => 'Pricol Limited',
                'designation' => 'General Manager - Manufacturing Operations',
                'mobile' => '9840998877',
                'email' => 'rajeshwar.n@pricol.co.in',
                'purpose' => 'Technical Centre Factory Automation & Robotic Review',
                'department' => 'Automation & Robotics Shop',
                'in_time' => '11:00:00',
            ],
            [
                'pass_code' => 'VIS-2026-0104',
                'name' => 'Sundaramoorthy G',
                'company' => 'Sundram Fasteners Limited',
                'designation' => 'Senior Vice President - Operations',
                'mobile' => '9840332211',
                'email' => 'sundaramoorthy.g@sundram.com',
                'purpose' => 'Customer Mould Trial & Production Cycle Time Optimization',
                'department' => 'Precision Tooling Facility',
                'in_time' => '11:45:00',
            ],
            [
                'pass_code' => 'VIS-2026-0105',
                'name' => 'Karthik Raman',
                'company' => 'TVS Motor Company',
                'designation' => 'Head - Advanced Tooling & Die Design',
                'mobile' => '9840456789',
                'email' => 'karthik.raman@tvsmotor.com',
                'purpose' => 'Electric Injection Moulding Machine Demonstration & Trial',
                'department' => 'Technical Centre',
                'in_time' => '13:30:00',
            ],
            [
                'pass_code' => 'VIS-2026-0106',
                'name' => 'Senthil Kumar',
                'company' => 'Tata Motors CVBU',
                'designation' => 'Lead Process & Production Engineer',
                'mobile' => '9840554433',
                'email' => 'senthil.kumar@tatamotors.com',
                'purpose' => 'Heavy Tonnage Moulding Machinery Capacity Assessment',
                'department' => 'Heavy Moulding Shop',
                'in_time' => '14:15:00',
            ],
            [
                'pass_code' => 'VIS-2026-0107',
                'name' => 'Venkatesh Raghavan',
                'company' => 'Brakes India Private Limited',
                'designation' => 'Chief Tooling Engineer',
                'mobile' => '9840667788',
                'email' => 'venkatesh.r@brakesindia.co.in',
                'purpose' => 'Annual Plant Tooling & Factory Automation Inspection',
                'department' => 'Tool Room',
                'in_time' => '15:00:00',
            ],
            [
                'pass_code' => 'VIS-2026-0108',
                'name' => 'Ananya Deshmukh',
                'company' => 'Supreme Industries Limited',
                'designation' => 'Senior Quality Auditor',
                'mobile' => '9840889900',
                'email' => 'ananya.d@supreme.co.in',
                'purpose' => 'High Precision Moulding Quality & Defect Analysis',
                'department' => 'Quality Assurance',
                'in_time' => '15:45:00',
            ],
            [
                'pass_code' => 'VIS-2026-0109',
                'name' => 'Praveen Kumar',
                'company' => 'Wheels India Limited',
                'designation' => 'Maintenance & Reliability Head',
                'mobile' => '9840223344',
                'email' => 'praveen.k@wheelsindia.com',
                'purpose' => 'Predictive Maintenance & IoT Spares Capability Review',
                'department' => 'Maintenance & Services',
                'in_time' => '16:30:00',
            ],
            [
                'pass_code' => 'VIS-2026-0110',
                'name' => 'Balachandar M',
                'company' => 'Ashok Leyland Limited',
                'designation' => 'Head of Strategic Sourcing & Procurement',
                'mobile' => '9840114477',
                'email' => 'balachandar.m@ashokleyland.com',
                'purpose' => 'Strategic Vendor Audit & Machinery Capacity Verification',
                'department' => 'Executive Plant Tour',
                'in_time' => '17:15:00',
            ],
        ];

        foreach ($visitorsData as $item) {
            // 1. Ensure Visitor Master Record exists (or update)
            $visitor = Visitor::where('mobile', $item['mobile'])->first();
            if (!$visitor) {
                $visitor = Visitor::create([
                    'visitor_id' => $item['pass_code'],
                    'name' => $item['name'],
                    'company' => $item['company'],
                    'designation' => $item['designation'],
                    'mobile' => $item['mobile'],
                    'email' => $item['email'],
                    'external_source' => 'plant_gate_pass',
                    'external_id' => $item['pass_code'],
                    'is_active' => true,
                ]);
            } else {
                $visitor->update([
                    'name' => $item['name'],
                    'company' => $item['company'],
                    'designation' => $item['designation'],
                    'email' => $item['email'],
                    'visitor_id' => $item['pass_code'],
                    'is_active' => true,
                ]);
            }

            // 2. Ensure Visit for Today exists without duplication
            $visit = Visit::where('visitor_id', $visitor->id)
                ->whereDate('visit_date', $today)
                ->first();

            if (!$visit) {
                Visit::create([
                    'visitor_id' => $visitor->id,
                    'visitor_code' => $visitor->visitor_id,
                    'organizer_id' => null, // Left unassigned so visitor selects their host organizer
                    'visit_date' => $today,
                    'in_time' => \Carbon\Carbon::parse($today . ' ' . $item['in_time']),
                    'out_time' => null,
                    'purpose' => $item['purpose'],
                    'department' => $item['department'],
                ]);
            } else {
                $visit->update([
                    'visitor_code' => $visitor->visitor_id,
                    'purpose' => $item['purpose'],
                    'department' => $item['department'],
                ]);
            }
        }
    }
}
