<?php

namespace Database\Seeders;

use App\Models\Banner;
use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanyAndBannerSeeder extends Seeder
{
    public function run(): void
    {
        Company::updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Shibaura Machine India Private Limited',
                'email' => 'customercare@shibaura-machine.co.in',
                'phone' => '+91 44 2681 2000',
                'alter_phone' => '+91 44 2681 2001',
                'address' => 'No. 65 (PO Box 14), Chennai-Bangalore Highway, Chembarambakkam',
                'city' => 'Chennai',
                'state' => 'Tamil Nadu',
                'pincode' => '600123',
                'website' => 'https://www.shibaura-machine.co.in',
                'logo' => 'images/shibaura-logo-cropped.webp',
                'description' => 'Precision injection moulding machines, die casting, and industrial robotic solutions engineered with Japanese precision.',
            ]
        );

        if (Banner::count() === 0) {
            Banner::create([
                'title' => 'Leading Precision Injection Moulding Solutions',
                'subtitle' => 'Empowering Indian Manufacturing with Japanese Engineering Excellence',
                'image_path' => 'images/shibaura-logo-cropped.webp',
                'target' => 'all',
                'link_url' => 'https://www.shibaura-machine.co.in',
                'sort_order' => 1,
                'is_active' => true,
            ]);

            Banner::create([
                'title' => 'Next-Gen Industrial Die Casting & Automation',
                'subtitle' => 'Zero-defect robotics and intelligent factory automation systems',
                'image_path' => 'images/shibaura-logo.webp',
                'target' => 'all',
                'link_url' => 'https://www.shibaura-machine.co.in',
                'sort_order' => 2,
                'is_active' => true,
            ]);

            Banner::create([
                'title' => 'Visitor Feedback & Experience Survey',
                'subtitle' => 'Your insights drive our engineering innovation and service excellence',
                'image_path' => 'images/shibaura-logo-cropped.webp',
                'target' => 'mobile',
                'link_url' => null,
                'sort_order' => 3,
                'is_active' => true,
            ]);
        }
    }
}
