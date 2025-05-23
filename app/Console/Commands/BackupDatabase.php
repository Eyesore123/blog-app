<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BackupDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:backup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backup the PostgreSQL database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting database backup...');
        
        // Create backups directory if it doesn't exist
        if (!Storage::exists('backups')) {
            Storage::makeDirectory('backups');
        }
        
        // Get database configuration
        $host = config('database.connections.pgsql.host');
        $port = config('database.connections.pgsql.port');
        $database = config('database.connections.pgsql.database');
        $username = config('database.connections.pgsql.username');
        $password = config('database.connections.pgsql.password');
        
        // Generate backup filename with timestamp
        $timestamp = now()->format('Y-m-d_H-i-s');
        $filename = "backup_{$timestamp}.sql";
        $backupPath = storage_path("app/backups/{$filename}");
        
        // Set PGPASSWORD environment variable
        putenv("PGPASSWORD={$password}");
        
        // Build the pg_dump command
        $command = "pg_dump -h {$host} -p {$port} -U {$username} -d {$database} -f {$backupPath}";
        
        // Execute the command
        $output = [];
        $returnVar = 0;
        exec($command . " 2>&1", $output, $returnVar);
        
        // Clear the PGPASSWORD environment variable
        putenv("PGPASSWORD");
        
        if ($returnVar !== 0) {
            $this->error('Backup failed: ' . implode("\n", $output));
            Log::error('Database backup failed', ['output' => $output]);
            return 1;
        }
        
        // Check if the backup file was created
        if (file_exists($backupPath)) {
            $fileSize = filesize($backupPath);
            $fileSizeFormatted = $fileSize > 1024 ? round($fileSize / 1024, 2) . " KB" : $fileSize . " bytes";
            
            $this->info("Backup completed successfully: {$filename} ({$fileSizeFormatted})");
            Log::info("Database backup completed", ['file' => $filename, 'size' => $fileSizeFormatted]);
            
            // Clean up old backups (keep only the last 5)
            $this->cleanupOldBackups();
            
            return 0;
        } else {
            $this->error("Backup file was not created");
            Log::error("Database backup file was not created");
            return 1;
        }
    }
    
    /**
     * Clean up old backups, keeping only the most recent ones.
     */
    protected function cleanupOldBackups($keep = 5)
    {
        $backups = Storage::files('backups');
        
        // Filter only SQL backup files
        $backups = array_filter($backups, function($file) {
            return preg_match('/backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $file);
        });
        
        // Sort by newest first
        usort($backups, function($a, $b) {
            return filemtime(storage_path("app/{$b}")) - filemtime(storage_path("app/{$a}"));
        });
        
        // Delete old backups
        if (count($backups) > $keep) {
            $toDelete = array_slice($backups, $keep);
            
            foreach ($toDelete as $file) {
                Storage::delete($file);
                $this->info("Deleted old backup: " . basename($file));
                Log::info("Deleted old backup", ['file' => basename($file)]);
            }
        }
    }
}
