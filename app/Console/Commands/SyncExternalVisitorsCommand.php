<?php

namespace App\Console\Commands;

use App\Services\ExternalVisitorSyncService;
use Illuminate\Console\Command;

class SyncExternalVisitorsCommand extends Command
{
    protected $signature = 'visitors:sync-external {--date= : The date to sync visitors for (YYYY-MM-DD)} {--organizer= : Default organizer/staff ID to assign}';
    protected $description = 'Poll & sync plant visitor data from external 3rd-party API into visitor records and shift feed.';

    public function handle(ExternalVisitorSyncService $syncService): int
    {
        $date = $this->option('date');
        $organizerId = $this->option('organizer') ? (int) $this->option('organizer') : null;

        $this->info("Fetching & syncing visitors from 3rd-party API" . ($date ? " for {$date}..." : "..."));

        $result = $syncService->syncVisitors($date, $organizerId);

        $this->info($result['message']);
        $this->line("Synced: {$result['synced_visitors']} | New visits: {$result['created_visits']} | Existing: {$result['existing_visits']}");

        if (!empty($result['active_shift'])) {
            $this->comment("Associated Active Shift: {$result['active_shift']}");
        }

        return Command::SUCCESS;
    }
}
