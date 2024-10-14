<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ImportDatabase extends Command
{
    // The name and signature of the console command
    protected $signature = 'database:import';

    // The description of the console command
    protected $description = 'Import SQL file into the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Define the path to the SQL file
        $sqlFile = base_path('shadcnvue.sql');

        // Check if the SQL file exists
        if (!file_exists($sqlFile)) {
            $this->error("SQL file not found: " . $sqlFile);
            return 1;
        }

        // Read the SQL file content
        $sql = file_get_contents($sqlFile);

        try {
            // Execute the SQL queries
            DB::unprepared($sql);
            $this->info("Database imported successfully!");
        } catch (\Exception $e) {
            // Catch any error during import
            $this->error("Error importing database: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
