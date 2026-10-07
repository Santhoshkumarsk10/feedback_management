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
        Schema::table('permissions', function (Blueprint $table) {
            if (!Schema::hasColumn('permissions', 'display_name')) {
                $table->string('display_name')->nullable()->after('name');
            }
            if (!Schema::hasColumn('permissions', 'module')) {
                $table->string('module', 50)->default('General')->after('display_name');
            }
            if (!Schema::hasColumn('permissions', 'description')) {
                $table->string('description', 255)->nullable()->after('module');
            }
        });

        // Seed readable metadata for standard permissions
        $metadata = [
            'access-web-panel' => [
                'display_name' => 'Access Web Panel',
                'module' => 'Platform Access',
                'description' => 'Login and view operations admin dashboard on web browser',
            ],
            'access-mobile-app' => [
                'display_name' => 'Access Mobile / Tablet App',
                'module' => 'Platform Access',
                'description' => 'Login to tablet / APK application for on-ground shift operations',
            ],
            'manage-users' => [
                'display_name' => 'Manage Users',
                'module' => 'System Masters',
                'description' => 'Create, update, toggle, and manage staff and admin user accounts',
            ],
            'manage-roles' => [
                'display_name' => 'Manage Roles & Permissions',
                'module' => 'System Masters',
                'description' => 'Create custom roles and configure capability permissions',
            ],
            'manage-shifts' => [
                'display_name' => 'Manage Shift Schedules',
                'module' => 'System Masters',
                'description' => 'Configure manufacturing plant operating shift timings (Shift A, B, C)',
            ],
            'manage-plants' => [
                'display_name' => 'Manage Plant Facilities',
                'module' => 'System Masters',
                'description' => 'Register and configure Shibaura Machine manufacturing plants',
            ],
            'manage-questions' => [
                'display_name' => 'Manage Evaluation Form',
                'module' => 'System Masters',
                'description' => 'Design customer visit feedback questionnaire sections and rating questions',
            ],
            'view-visits' => [
                'display_name' => 'View Plant Visitors',
                'module' => 'Operations & Visits',
                'description' => 'View plant visit logs, today’s visitor list, and tour history',
            ],
            'manage-visits' => [
                'display_name' => 'Register & Manage Visits',
                'module' => 'Operations & Visits',
                'description' => 'Register visitor entries and update tour escort details',
            ],
            'view-feedbacks' => [
                'display_name' => 'View Customer Reviews',
                'module' => 'Operations & Visits',
                'description' => 'Inspect submitted customer feedback ratings and evaluation answers',
            ],
            'submit-feedback' => [
                'display_name' => 'Submit Feedback for Visitor',
                'module' => 'Operations & Visits',
                'description' => 'Assist visitors or submit evaluation responses on behalf of visitor',
            ],
            'view-reports' => [
                'display_name' => 'View Analytics Reports',
                'module' => 'Analytics & Insights',
                'description' => 'View satisfaction breakdown, NPS metrics, and plant comparison analytics',
            ],
            'export-reports' => [
                'display_name' => 'Export Reports Data',
                'module' => 'Analytics & Insights',
                'description' => 'Export visit statistics and satisfaction data in CSV format',
            ],
            'manage-company' => [
                'display_name' => 'Manage Company Profile',
                'module' => 'Brand & Content',
                'description' => 'Update Shibaura Machine branding, contact details, and logo',
            ],
            'manage-banners' => [
                'display_name' => 'Manage CMS Banners',
                'module' => 'Brand & Content',
                'description' => 'Upload and order tablet and web promotional banners',
            ],
            'view-audit-logs' => [
                'display_name' => 'View Security Audit Trail',
                'module' => 'Security & Audits',
                'description' => 'Inspect administrative audit log trail and security records',
            ],
        ];

        foreach ($metadata as $permName => $data) {
            DB::table('permissions')->where('name', $permName)->update($data);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropColumn(['display_name', 'module', 'description']);
        });
    }
};
