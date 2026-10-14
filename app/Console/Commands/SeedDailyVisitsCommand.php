<?php

namespace App\Console\Commands;

use Database\Seeders\DailyVisitsSeeder;
use Illuminate\Console\Command;

class SeedDailyVisitsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'plant:seed-daily-visits';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed exactly 10 realistic, unique plant visitor records for today with unassigned host organizers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Seeding 10 realistic plant visits for today...");
        $seeder = new DailyVisitsSeeder();
        $seeder->run();
        $this->info("Successfully seeded 10 non-duplicate plant visits for today!");
        return Command::SUCCESS;
    }
}
