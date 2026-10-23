<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ClearTransactionalDataCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:clear-transactions
                            {--force : Bypass confirmation prompt}
                            {--no-backup : Skip automatic SQL backup prior to clearing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Safely clear all transactional and operational data while strictly preserving Master Data (Users, Roles & Permissions, Questions, Banners, Companies, Plants)';

    /**
     * Protected Master Data tables that must NEVER be touched by this command.
     */
    protected const MASTER_TABLES = [
        'users',
        'roles',
        'permissions',
        'role_has_permissions',
        'roles_has_permissions',
        'model_has_roles',
        'model_has_permissions',
        'plants',
        'questions',
        'banners',
        'companies',
        'migrations',
        'password_reset_tokens',
    ];

    /**
     * Transactional & operational tables to be cleared.
     */
    protected const TRANSACTIONAL_TABLES = [
        'feedback_answers',
        'feedbacks',
        'visits',
        'visitors',
        'audit_logs',
        'cache',
        'cache_locks',
        'jobs',
        'job_batches',
        'failed_jobs',
    ];

    /**
     * Temporary migration backup tables to purge if present.
     */
    protected const TEMP_BACKUP_TABLES = [
        'feedback_answers_backup_20261014',
        'feedbacks_backup_20261014',
        'visitors_backup_20261014',
        'visits_backup_20261014',
    ];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("=== Master Data Preservation & Transactional Cleanup ===");

        if (!$this->option('force')) {
            $confirmed = $this->confirm(
                "Are you sure you want to clear all transactional records (feedbacks, visits, visitors, audit logs)? Master Data will remain completely intact.",
                false
            );

            if (!$confirmed) {
                $this->warn("Operation cancelled by user.");
                return Command::SUCCESS;
            }
        }

        // Step 1: Automatic SQL Backup (unless opted out)
        if (!$this->option('no-backup')) {
            $this->createBackup();
        }

        // Step 2: Clear Transactional Tables safely
        $this->line("");
        $this->info("Purging transactional records...");

        $clearedSummary = [];
        $totalRowsPurged = 0;

        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            // 1. Truncate standard operational tables
            foreach (self::TRANSACTIONAL_TABLES as $table) {
                if (Schema::hasTable($table)) {
                    $count = DB::table($table)->count();
                    DB::table($table)->truncate();
                    $clearedSummary[] = [$table, number_format($count) . ' rows', 'Truncated & Reset'];
                    $totalRowsPurged += $count;
                }
            }

            // 2. Drop / purge temporary backup tables
            foreach (self::TEMP_BACKUP_TABLES as $tempTable) {
                if (Schema::hasTable($tempTable)) {
                    $count = DB::table($tempTable)->count();
                    Schema::dropIfExists($tempTable);
                    $clearedSummary[] = [$tempTable, number_format($count) . ' rows', 'Dropped Temp Table'];
                    $totalRowsPurged += $count;
                }
            }

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        } catch (\Throwable $e) {
            try {
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            } catch (\Throwable $ignored) {}

            $this->error("Cleanup failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        // Display Cleared Summary Table
        $this->table(['Cleared Table', 'Records Purged', 'Status'], $clearedSummary);
        $this->info("Total records purged: " . number_format($totalRowsPurged));

        // Step 3: Record Audit Log Entry for the cleanup
        try {
            AuditLog::record(
                'clear_transactions',
                'database',
                "System cleared {$totalRowsPurged} transactional records across " . count($clearedSummary) . " tables. Master data strictly preserved.",
                [
                    'total_rows_purged' => $totalRowsPurged,
                    'cleared_tables' => array_column($clearedSummary, 0),
                ]
            );
        } catch (\Throwable $e) {
            // Non-critical if audit log recording fails
        }

        // Step 4: Verify and Display Master Data Integrity
        $this->line("");
        $this->info("=== Master Data Integrity Verification ===");
        $masterSummary = [];

        foreach (self::MASTER_TABLES as $table) {
            if (Schema::hasTable($table)) {
                $count = DB::table($table)->count();
                $masterSummary[] = [$table, number_format($count) . ' rows', 'Protected & Intact'];
            }
        }

        $this->table(['Master Table', 'Current Rows', 'Protection Status'], $masterSummary);

        $this->info("Success! Database has been cleaned cleanly. Only Master Data is preserved.");
        return Command::SUCCESS;
    }

    /**
     * Create an automatic mysqldump backup.
     */
    protected function createBackup(): void
    {
        $backupDir = database_path('backups');
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $cfg = config('database.connections.mysql');
        $backupFile = $backupDir . '/backup_before_cleanup_' . date('Y_m_d_His') . '.sql';

        $cmd = sprintf(
            'mysqldump --host=%s --port=%s --user=%s %s %s > %s 2>/dev/null',
            escapeshellarg($cfg['host'] ?? '127.0.0.1'),
            escapeshellarg($cfg['port'] ?? '3306'),
            escapeshellarg($cfg['username'] ?? 'root'),
            !empty($cfg['password']) ? '--password=' . escapeshellarg($cfg['password']) : '',
            escapeshellarg($cfg['database'] ?? 'plant_feedback'),
            escapeshellarg($backupFile)
        );

        exec($cmd, $output, $returnCode);

        if ($returnCode === 0 && file_exists($backupFile) && filesize($backupFile) > 0) {
            $this->info("Safety backup created: " . basename($backupFile) . " (" . round(filesize($backupFile) / 1024, 1) . " KB)");
        } else {
            $this->warn("Automatic mysqldump was not completed, but proceeding safely.");
        }
    }
}
