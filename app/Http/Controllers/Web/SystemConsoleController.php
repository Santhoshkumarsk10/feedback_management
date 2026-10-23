<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Feedback;
use App\Models\Plant;
use App\Models\User;
use App\Models\Visit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class SystemConsoleController extends Controller
{
    /**
     * Whitelist of safe allowed Artisan commands for web execution.
     */
    protected const ALLOWED_COMMANDS = [
        'optimize:clear' => 'Clearing cached bootstrap, routes, views, and configurations...',
        'optimize' => 'Pre-compiling routes, configuration files, and blade templates...',
        'migrate' => 'Running database schema migrations...',
        'db:seed' => 'Executing database seeders...',
        'plant:seed-daily-visits' => 'Seeding 10 realistic visitor entries for today...',
        'visitors:sync-external' => 'Synchronizing external visitor records...',
        'storage:link' => 'Generating public/storage symlink...',
        'config:cache' => 'Caching application configuration...',
        'config:clear' => 'Clearing configuration cache...',
        'route:cache' => 'Caching application routing table...',
        'route:clear' => 'Clearing route cache...',
        'view:cache' => 'Pre-compiling Blade views...',
        'view:clear' => 'Clearing compiled view files...',
        'event:cache' => 'Caching application event listeners...',
        'event:clear' => 'Clearing event listener cache...',
        'cache:clear' => 'Flushing application key-value cache store...',
        'db:clear-transactions' => 'Safely clearing transactional tables and preserving Master Data...',
        'up' => 'Bringing application out of maintenance mode (Live)...',
        'down' => 'Putting application into maintenance mode...',
    ];

    /**
     * Display the System Maintenance & Artisan Console.
     */
    public function index(Request $request): View
    {
        $metrics = $this->gatherSystemMetrics();
        $seeders = $this->getAvailableSeeders();
        $coordinators = User::whereIn('role', ['coordinator', 'organizer', 'staff', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name', 'role']);

        $isStandalone = $request->boolean('standalone') || $request->routeIs('system_console.standalone') || !auth()->check();

        return view('maintenance.system_console', [
            'metrics' => $metrics,
            'seeders' => $seeders,
            'coordinators' => $coordinators,
            'isStandalone' => $isStandalone,
            'allowedCommands' => self::ALLOWED_COMMANDS,
        ]);
    }

    /**
     * Standalone direct access endpoint.
     */
    public function standaloneAccess(Request $request)
    {
        // 1. Allow if authenticated with admin/superadmin role
        if (auth()->check()) {
            $user = auth()->user();
            if ($user->hasAnyRole(['superadmin', 'admin']) || in_array($user->role, ['superadmin', 'admin'], true)) {
                return $this->index($request);
            }
        }

        // 2. Allow if valid developer key provided in query string or session
        $devKey = $request->query('key') ?? session('dev_console_authorized_key');
        $validKey = env('DEV_CONSOLE_KEY') ?: substr(md5(config('app.key') . 'system_console'), 0, 16);

        if ($devKey && hash_equals($validKey, (string) $devKey)) {
            session(['dev_console_authorized_key' => $validKey]);
            return $this->index($request);
        }

        // 3. Otherwise redirect to login with notification
        return redirect()->route('login')->with('info', 'System Console requires Superadmin or Administrator authentication.');
    }

    /**
     * Execute an Artisan command safely and capture its output.
     */
    public function executeCommand(Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureAuthorized($request);

        $request->validate([
            'command' => ['required', 'string'],
            'seeder' => ['nullable', 'string'],
            'sync_date' => ['nullable', 'date_format:Y-m-d'],
            'organizer_id' => ['nullable', 'integer'],
            'force' => ['nullable', 'boolean'],
        ]);

        $command = trim($request->input('command'));

        // Whitelist check
        if (!array_key_exists($command, self::ALLOWED_COMMANDS)) {
            $errorMsg = "Unauthorized command: '{$command}' is not in the safe execution whitelist.";
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => $errorMsg], 422);
            }
            return back()->with('error', $errorMsg);
        }

        // Build command parameters
        $params = [];
        $displayCommand = "php artisan {$command}";

        if ($command === 'migrate') {
            $params['--force'] = true;
            $displayCommand .= ' --force';
        } elseif ($command === 'db:seed') {
            $params['--force'] = true;
            $seeder = $request->input('seeder');
            if (!empty($seeder) && $seeder !== 'all') {
                $params['--class'] = $seeder;
                $displayCommand .= " --class={$seeder} --force";
            } else {
                $displayCommand .= ' --force';
            }
        } elseif ($command === 'visitors:sync-external') {
            if ($request->filled('sync_date')) {
                $params['--date'] = $request->input('sync_date');
                $displayCommand .= " --date={$params['--date']}";
            }
            if ($request->filled('organizer_id')) {
                $params['--organizer'] = $request->input('organizer_id');
                $displayCommand .= " --organizer={$params['--organizer']}";
            }
        }

        $startTime = microtime(true);
        $exitCode = 0;
        $output = '';

        try {
            $exitCode = Artisan::call($command, $params);
            $output = Artisan::output();
            if (empty(trim($output))) {
                $output = "Command '{$displayCommand}' executed successfully with exit code 0 (no output returned).";
            }
        } catch (\Throwable $e) {
            $exitCode = 1;
            $output = "Execution failed with exception: " . $e->getMessage() . "\n\nStack Trace:\n" . $e->getTraceAsString();
        }

        $durationMs = round((microtime(true) - $startTime) * 1000);

        $actorName = auth()->check() ? auth()->user()->name : 'Direct Developer Console';
        AuditLog::record(
            'execute',
            'system_console',
            "{$actorName} ran console command: {$displayCommand} (Exit: {$exitCode}, Duration: {$durationMs}ms)",
            [
                'command' => $displayCommand,
                'exit_code' => $exitCode,
                'duration_ms' => $durationMs,
                'ip' => $request->ip(),
            ]
        );

        $isSuccess = ($exitCode === 0);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => $isSuccess,
                'command' => $displayCommand,
                'output' => $output,
                'exit_code' => $exitCode,
                'duration_ms' => $durationMs,
                'timestamp' => now()->toIso8601String(),
            ]);
        }

        $flashKey = $isSuccess ? 'success' : 'error';
        $flashMsg = $isSuccess
            ? "Command '{$displayCommand}' executed successfully in {$durationMs}ms!"
            : "Command '{$displayCommand}' failed with exit code {$exitCode}.";

        return back()
            ->with($flashKey, $flashMsg)
            ->with('command_output', $output)
            ->with('command_name', $displayCommand)
            ->with('command_duration', $durationMs);
    }

    /**
     * Direct GET action: Clear All Caches.
     */
    public function directClearCache(Request $request): RedirectResponse
    {
        $this->ensureAuthorized($request);
        Artisan::call('optimize:clear');
        $output = Artisan::output();

        return redirect()->route('system_console.index')
            ->with('success', 'All system caches have been completely cleared! Blade, routes, configs, and events are fresh.')
            ->with('command_output', $output)
            ->with('command_name', 'php artisan optimize:clear');
    }

    /**
     * Direct GET action: Optimize Application.
     */
    public function directOptimize(Request $request): RedirectResponse
    {
        $this->ensureAuthorized($request);
        Artisan::call('optimize');
        $output = Artisan::output();

        return redirect()->route('system_console.index')
            ->with('success', 'Application successfully compiled and optimized for production speed!')
            ->with('command_output', $output)
            ->with('command_name', 'php artisan optimize');
    }

    /**
     * Direct GET action: Run Migrations.
     */
    public function directMigrate(Request $request): RedirectResponse
    {
        $this->ensureAuthorized($request);
        Artisan::call('migrate', ['--force' => true]);
        $output = Artisan::output();

        return redirect()->route('system_console.index')
            ->with('success', 'Database migrations executed successfully.')
            ->with('command_output', $output)
            ->with('command_name', 'php artisan migrate --force');
    }

    /**
     * Direct GET action: Seed Daily Visits.
     */
    public function directSeedDailyVisits(Request $request): RedirectResponse
    {
        $this->ensureAuthorized($request);
        Artisan::call('plant:seed-daily-visits');
        $output = Artisan::output();

        return redirect()->route('system_console.index')
            ->with('success', 'Successfully seeded 10 realistic visitor entries for today!')
            ->with('command_output', $output)
            ->with('command_name', 'php artisan plant:seed-daily-visits');
    }

    /**
     * Direct GET action: Sync External Visitors.
     */
    public function directSyncExternal(Request $request): RedirectResponse
    {
        $this->ensureAuthorized($request);
        Artisan::call('visitors:sync-external');
        $output = Artisan::output();

        return redirect()->route('system_console.index')
            ->with('success', 'External visitor synchronization executed successfully.')
            ->with('command_output', $output)
            ->with('command_name', 'php artisan visitors:sync-external');
    }

    /**
     * Direct GET action: Storage Link.
     */
    public function directStorageLink(Request $request): RedirectResponse
    {
        $this->ensureAuthorized($request);
        Artisan::call('storage:link');
        $output = Artisan::output();

        return redirect()->route('system_console.index')
            ->with('success', 'Public storage symlink linked successfully!')
            ->with('command_output', $output)
            ->with('command_name', 'php artisan storage:link');
    }

    /**
     * View recent system log lines.
     */
    public function viewLogs(Request $request): JsonResponse|View
    {
        $this->ensureAuthorized($request);

        $logPath = $this->getActiveLogFilePath();
        $linesCount = (int) $request->input('lines', 150);
        $logContent = '';
        $logSize = '0 KB';

        if (File::exists($logPath)) {
            $logSize = $this->formatBytes(File::size($logPath));
            $fileLines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            if (is_array($fileLines)) {
                $slice = array_slice($fileLines, -$linesCount);
                $logContent = implode("\n", $slice);
            }
        } else {
            $logContent = "No log file found at: {$logPath}";
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'path' => basename($logPath),
                'size' => $logSize,
                'lines' => $linesCount,
                'content' => $logContent,
            ]);
        }

        return view('maintenance.system_logs', [
            'logContent' => $logContent,
            'logPath' => $logPath,
            'logSize' => $logSize,
            'linesCount' => $linesCount,
        ]);
    }

    /**
     * Empty / Clear the active Laravel log file.
     */
    public function clearLogs(Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureAuthorized($request);

        $logPath = $this->getActiveLogFilePath();
        $success = false;

        if (File::exists($logPath)) {
            File::put($logPath, '');
            $success = true;
        }

        $actorName = auth()->check() ? auth()->user()->name : 'Direct Developer Console';
        AuditLog::record(
            'clear_logs',
            'system_console',
            "{$actorName} cleared system log file: " . basename($logPath),
            ['log_file' => basename($logPath)]
        );

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'System log file was successfully cleared.',
            ]);
        }

        return back()->with('success', 'System log file has been flushed and cleared.');
    }

    /**
     * Download the active Laravel log file.
     */
    public function downloadLogs(Request $request)
    {
        $this->ensureAuthorized($request);

        $logPath = $this->getActiveLogFilePath();
        if (!File::exists($logPath)) {
            return back()->with('error', 'Log file does not exist to download.');
        }

        return response()->download($logPath, 'laravel-' . date('Y-m-d_His') . '.log', [
            'Content-Type' => 'text/plain',
        ]);
    }

    /**
     * Toggle Maintenance mode (Up / Down).
     */
    public function toggleMaintenance(Request $request): JsonResponse|RedirectResponse
    {
        $this->ensureAuthorized($request);

        $isDown = app()->isDownForMaintenance();
        if ($isDown) {
            Artisan::call('up');
            $msg = 'Application is now LIVE and accessible to all users.';
        } else {
            $secret = $request->input('secret', 'shibaura-bypass-' . substr(md5(config('app.key')), 0, 8));
            Artisan::call('down', [
                '--secret' => $secret,
                '--render' => 'errors::503',
            ]);
            $msg = "Application placed into MAINTENANCE mode! Bypass secret URL: " . url("/{$secret}");
        }

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_down' => !$isDown,
                'message' => $msg,
            ]);
        }

        return back()->with('success', $msg);
    }

    /**
     * Security authorization guard: Allows direct developer execution without auth as requested.
     */
    protected function ensureAuthorized(Request $request): void
    {
        // Unrestricted developer console access
        return;
    }

    /**
     * Gather system diagnostic indicators.
     */
    protected function gatherSystemMetrics(): array
    {
        $dbConnection = config('database.default', 'mysql');
        $dbName = config("database.connections.{$dbConnection}.database", 'unknown');

        // Test database connectivity
        $dbConnected = false;
        try {
            DB::connection()->getPdo();
            $dbConnected = true;
        } catch (\Throwable $e) {
            $dbConnected = false;
        }

        $logPath = $this->getActiveLogFilePath();
        $logSizeFormatted = File::exists($logPath) ? $this->formatBytes(File::size($logPath)) : '0 KB';
        $logSizeBytes = File::exists($logPath) ? File::size($logPath) : 0;

        $storageWritable = is_writable(storage_path());
        $bootstrapCacheWritable = is_writable(base_path('bootstrap/cache'));

        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'app_env' => strtoupper(config('app.env', 'production')),
            'app_debug' => (bool) config('app.debug', false),
            'app_url' => config('app.url', 'http://localhost'),
            'db_connection' => strtoupper($dbConnection),
            'db_name' => $dbName,
            'db_connected' => $dbConnected,
            'cache_driver' => ucfirst(config('cache.default', 'database')),
            'session_driver' => ucfirst(config('session.driver', 'database')),
            'queue_driver' => ucfirst(config('queue.default', 'database')),
            'storage_permissions' => $storageWritable ? 'Writable (OK)' : 'Read-Only (Error)',
            'storage_writable' => $storageWritable,
            'bootstrap_cache_writable' => $bootstrapCacheWritable,
            'log_size' => $logSizeFormatted,
            'log_size_bytes' => $logSizeBytes,
            'log_file_name' => basename($logPath),
            'is_maintenance' => app()->isDownForMaintenance(),
            'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Nginx/Apache',
            'counts' => [
                'users' => User::count(),
                'plants' => Plant::count(),
                'visits' => Visit::count(),
                'feedbacks' => Feedback::count(),
            ],
        ];
    }

    /**
     * Locate the most active log file in storage/logs.
     */
    protected function getActiveLogFilePath(): string
    {
        $todayDailyLog = storage_path('logs/laravel-' . date('Y-m-d') . '.log');
        if (File::exists($todayDailyLog)) {
            return $todayDailyLog;
        }

        $singleLog = storage_path('logs/laravel.log');
        if (File::exists($singleLog)) {
            return $singleLog;
        }

        $allLogs = glob(storage_path('logs/laravel*.log'));
        if (!empty($allLogs)) {
            usort($allLogs, fn($a, $b) => filemtime($b) - filemtime($a));
            return $allLogs[0];
        }

        return $singleLog;
    }

    /**
     * Get list of database seeders available to execute.
     */
    protected function getAvailableSeeders(): array
    {
        return [
            'all' => [
                'label' => 'Run Master Data (MasterSeeder - All)',
                'class' => 'Database\Seeders\MasterSeeder',
                'description' => 'Executes comprehensive seeding: plants, roles, permissions, users, banners, and default data.',
            ],
            'Database\Seeders\RoleAndPermissionSeeder' => [
                'label' => 'Roles & RBAC Permissions Seeder',
                'class' => 'Database\Seeders\RoleAndPermissionSeeder',
                'description' => 'Re-seeds system roles, modules, and 14 permission matrices for Superadmin, Admin, and Coordinator.',
            ],
            'Database\Seeders\PlantSeeder' => [
                'label' => 'Plant Master Seeder',
                'class' => 'Database\Seeders\PlantSeeder',
                'description' => 'Seeds all official Shibaura manufacturing plants, technical centers, and divisions.',
            ],
            'Database\Seeders\CompanyAndBannerSeeder' => [
                'label' => 'Company & CMS Banners Seeder',
                'class' => 'Database\Seeders\CompanyAndBannerSeeder',
                'description' => 'Seeds corporate company profile information, branding, and promotional banners.',
            ],
            'Database\Seeders\DailyVisitsSeeder' => [
                'label' => "Today's Daily Plant Visits Seeder",
                'class' => 'Database\Seeders\DailyVisitsSeeder',
                'description' => 'Seeds exactly 10 realistic visitor entries for today with unassigned host organizers.',
            ],
            'Database\Seeders\AuditLogSeeder' => [
                'label' => 'Audit Log Trail Seeder',
                'class' => 'Database\Seeders\AuditLogSeeder',
                'description' => 'Seeds demonstration administrative audit trails and security activity logs.',
            ],
        ];
    }

    /**
     * Human-readable byte formatting.
     */
    protected function formatBytes(int $bytes, int $precision = 1): string
    {
        if ($bytes <= 0) {
            return '0 KB';
        }

        $units = ['B', 'KB', 'MB', 'GB'];
        $power = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        $power = min($power, count($units) - 1);

        return round($bytes / pow(1024, $power), $precision) . ' ' . $units[$power];
    }
}
