<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseMaintenanceController extends Controller
{
    /**
     * Exact tables strictly protected from any truncation/clear operations.
     * Master data (Users, Roles, Permissions, Questions, Banners, Companies, Plants)
     * are shielded here to prevent accidental data loss.
     */
    public const PROTECTED_TABLES = [
        'users',
        'password_reset_tokens',
        'role_has_permissions',
        'roles_has_permissions',
        'permissions',
        'roles',
        'model_has_roles',
        'model_has_permissions',
        'plants',
        'questions',
        'banners',
        'companies',
        'sessions',
        'migrations',
    ];

    /**
     * Human-friendly descriptions and categorization reasons.
     */
    protected array $tableMetadata = [
        'users' => [
            'description' => 'User accounts, hashed passwords, roles and plant assignments.',
            'protected_reason' => 'System authentication & security access control core.',
        ],
        'password_reset_tokens' => [
            'description' => 'Active password reset authentication tokens.',
            'protected_reason' => 'Active security credentials & reset authorization.',
        ],
        'role_has_permissions' => [
            'description' => 'RBAC mapping linking permissions to system roles.',
            'protected_reason' => 'Spatie authorization & capability mapping.',
        ],
        'permissions' => [
            'description' => 'System permission capabilities and access tokens.',
            'protected_reason' => 'Core security matrix definition.',
        ],
        'sessions' => [
            'description' => 'Active user session states and remember tokens.',
            'protected_reason' => 'Prevents immediate disconnect and session invalidation.',
        ],
        'migrations' => [
            'description' => 'Executed database migration version history.',
            'protected_reason' => 'Schema integrity and Laravel migration tracker.',
        ],
        'roles' => [
            'description' => 'Defined access tiers (Super Admin, Admin, Supervisor, Staff, Coordinator).',
            'protected_reason' => 'System role definitions.',
        ],
        'model_has_roles' => [
            'description' => 'User to role assignments.',
            'protected_reason' => 'Security role linking.',
        ],
        'model_has_permissions' => [
            'description' => 'Direct user to permission assignments.',
            'protected_reason' => 'Direct capability assignments.',
        ],
        'plants' => [
            'description' => 'Manufacturing plants, technical centres, and machine divisions.',
            'protected_reason' => 'Core Master Data: Shibaura plant facilities and division units.',
        ],
        'questions' => [
            'description' => 'Feedback questionnaire items, star ratings, and sections.',
            'protected_reason' => 'Core Master Data: Standard survey questionnaire and rating items.',
        ],
        'banners' => [
            'description' => 'Promotional banners and welcome displays on customer forms.',
            'protected_reason' => 'Core Master Data: CMS brand banners and visual identity.',
        ],
        'companies' => [
            'description' => 'Corporate branding, logos, contact details, and display configs.',
            'protected_reason' => 'Core Master Data: Corporate identity, contact details, and branding.',
        ],
        // Unshielded tables (Operational & Transactional Data)
        'visits' => [
            'description' => 'Plant visitor tour records, company details, arrival and status.',
            'category' => 'Operational Logs',
        ],
        'feedbacks' => [
            'description' => 'Customer feedback submissions, ratings, and qualitative comments.',
            'category' => 'Operational Logs',
        ],
        'feedback_answers' => [
            'description' => 'Itemized question responses, MCQ choices, and ratings.',
            'category' => 'Operational Logs',
        ],
        'visitors' => [
            'description' => 'Registered visitor identity records and company links.',
            'category' => 'Operational Logs',
        ],
        'audit_logs' => [
            'description' => 'Historical audit trail of administrative actions and user events.',
            'category' => 'System & Audit Logs',
        ],
        'cache' => [
            'description' => 'Application key-value cache store.',
            'category' => 'System Cache',
        ],
        'cache_locks' => [
            'description' => 'Atomic lock mutexes for concurrent tasks.',
            'category' => 'System Cache',
        ],
        'jobs' => [
            'description' => 'Queued background worker jobs.',
            'category' => 'Background Jobs',
        ],
        'job_batches' => [
            'description' => 'Batch job tracking states.',
            'category' => 'Background Jobs',
        ],
        'failed_jobs' => [
            'description' => 'Recorded failed background jobs with stack traces.',
            'category' => 'Background Jobs',
        ],
    ];

    /**
     * Render Table Cleanup & Maintenance Control Panel
     */
    public function index()
    {
        $migrationFiles = glob(database_path('migrations/*.php'));
        $migrationMap = [];

        foreach ($migrationFiles as $file) {
            $content = file_get_contents($file);
            if (preg_match_all("/Schema::create\(\s*['\"]([^'\"]+)['\"]\s*,/", $content, $matches)) {
                foreach ($matches[1] as $tbl) {
                    $migrationMap[$tbl] = basename($file);
                }
            }
        }

        // Query all tables from current database
        $dbName = config('database.connections.mysql.database', 'plant_feedback');
        $dbTables = DB::select("SELECT table_name FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
        $existingTables = array_map(fn($r) => $r->TABLE_NAME ?? $r->table_name, $dbTables);

        $unshieldedTables = [];
        $protectedTables = [];
        $totalUnshieldedRows = 0;
        $totalProtectedRows = 0;

        foreach ($existingTables as $tblName) {
            $isProtected = in_array(strtolower($tblName), self::PROTECTED_TABLES, true);
            $rowCount = 0;

            try {
                $rowCount = DB::table($tblName)->count();
            } catch (\Throwable $e) {
                // Ignore if table temporarily locked
            }

            $meta = $this->tableMetadata[$tblName] ?? [];
            $tableData = [
                'name' => $tblName,
                'rows' => $rowCount,
                'is_protected' => $isProtected,
                'migration' => $migrationMap[$tblName] ?? 'System / Engine',
                'description' => $meta['description'] ?? 'Application database table.',
                'protected_reason' => $meta['protected_reason'] ?? 'Protected authentication or authorization security core.',
                'category' => $meta['category'] ?? ($isProtected ? 'Security Core' : 'Application Data'),
            ];

            if ($isProtected) {
                $protectedTables[] = $tableData;
                $totalProtectedRows += $rowCount;
            } else {
                $unshieldedTables[] = $tableData;
                $totalUnshieldedRows += $rowCount;
            }
        }

        // Sort alphabetically
        usort($unshieldedTables, fn($a, $b) => strcmp($a['name'], $b['name']));
        usort($protectedTables, fn($a, $b) => strcmp($a['name'], $b['name']));

        return view('maintenance.database_cleanup', [
            'unshieldedTables' => $unshieldedTables,
            'protectedTables' => $protectedTables,
            'totalUnshieldedRows' => $totalUnshieldedRows,
            'totalProtectedRows' => $totalProtectedRows,
            'protectedList' => self::PROTECTED_TABLES,
        ]);
    }

    /**
     * Clear / Truncate selected unshielded tables
     */
    public function clearTables(Request $request)
    {
        $request->validate([
            'tables' => ['required', 'array', 'min:1'],
            'tables.*' => ['string', 'regex:/^[a-zA-Z0-9_]+$/'],
        ], [
            'tables.required' => 'Please select at least one table to clear.',
            'tables.min' => 'Please select at least one table to clear.',
        ]);

        $selected = $request->input('tables', []);
        $cleared = [];
        $totalRowsRemoved = 0;

        // Security Validation: Absolute check against protected list
        foreach ($selected as $table) {
            if (in_array(strtolower($table), self::PROTECTED_TABLES, true)) {
                AuditLog::record(
                    'denied',
                    'database',
                    "Blocked attempt by " . auth()->user()->name . " to clear protected table: {$table}",
                    ['attempted_table' => $table]
                );

                return back()->with('error', "Security Violation: Table '{$table}' is strictly PROTECTED and cannot be cleared under any circumstance!");
            }

            if (!Schema::hasTable($table)) {
                return back()->with('error', "Table '{$table}' does not exist in the database.");
            }
        }

        // Execute table truncation safely with Foreign Key check toggle
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($selected as $table) {
                $count = DB::table($table)->count();
                DB::table($table)->truncate();
                $cleared[] = "{$table} ({$count} rows)";
                $totalRowsRemoved += $count;
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Throwable $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Throwable $ignored) {}

            return back()->with('error', 'Database execution failed: ' . $e->getMessage());
        }

        // Record Audit Trail
        AuditLog::record(
            'truncate',
            'database',
            "Administrator " . auth()->user()->name . " cleared " . count($selected) . " table(s) (" . implode(', ', $selected) . ") totaling {$totalRowsRemoved} rows.",
            [
                'cleared_tables' => $selected,
                'total_rows_removed' => $totalRowsRemoved,
            ]
        );

        return back()->with('success', "Operation completed! Successfully cleared " . count($selected) . " table(s) [" . implode(', ', $selected) . "]. Total {$totalRowsRemoved} records were purged.");
    }

    /**
     * Quick Re-seed / Restore Default Master Data
     */
    public function reseed(Request $request)
    {
        try {
            Artisan::call('db:seed', ['--force' => true]);

            AuditLog::record(
                'reseed',
                'database',
                "Administrator " . auth()->user()->name . " triggered Master Database Re-seed.",
                ['action' => 'db:seed']
            );

            return back()->with('success', 'Master data has been successfully re-seeded and restored! Default plants, questions, and accounts are ready.');
        } catch (\Throwable $e) {
            return back()->with('error', 'Re-seed operation failed: ' . $e->getMessage());
        }
    }
}
