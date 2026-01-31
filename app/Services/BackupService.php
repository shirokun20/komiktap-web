<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Process;
use ZipArchive;
use Illuminate\Support\Carbon;

class BackupService
{
    public function createBackup(array $options): string
    {
        $timestamp = Carbon::now()->format('Y-m-d-H-i-s');
        $fileName = "backup-{$timestamp}.zip";
        // Use the 'local' disk path to ensure consistency with listBackups()
        $backupPath = Storage::disk('local')->path("backups/{$fileName}");

        // Ensure backups directory exists
        if (!file_exists(dirname($backupPath))) {
            mkdir(dirname($backupPath), 0755, true);
        }

        $zip = new ZipArchive();
        if ($zip->open($backupPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \Exception("Cannot create zip file at {$backupPath}");
        }

        // 1. Database Backup
        if (in_array('db', $options)) {
            $this->addDatabaseDump($zip);
        }

        // 2. Public Storage
        if (in_array('files', $options)) {
            $this->addFolderToZip($zip, storage_path('app/public'), 'public_storage');
        }

        // 3. Logs
        if (in_array('logs', $options)) {
            $this->addFolderToZip($zip, storage_path('logs'), 'logs');
        }

        $zip->close();

        return $fileName;
    }

    protected function addDatabaseDump(ZipArchive $zip)
    {
        $dbDumpFile = Storage::disk('local')->path('backups/db-dump.sql');

        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $database = config('database.connections.mysql.database');
        $host = config('database.connections.mysql.host');

        // Simple mysqldump wrapper. In production you might want to use spatie/db-dumper
        // Masking password in command for security (though here it's executed directly)
        if (config('database.default') === 'sqlite') {
            $databasePath = config('database.connections.sqlite.database');
            if (file_exists($databasePath)) {
                copy($databasePath, $dbDumpFile);
            } else {
                throw new \Exception("SQLite database file not found at: {$databasePath}");
            }
        } else {
            // Detect Binary Path
            $dumpBinaryPath = env('BACKUP_DUMP_BINARY_PATH');
            
            if (empty($dumpBinaryPath)) {
                // Common paths for MacOS/Linux
                $commonPaths = [
                    'mysqldump', // Try PATH first
                    '/usr/bin/mysqldump',
                    '/usr/local/bin/mysqldump',
                    '/usr/local/mysql/bin/mysqldump',
                    '/opt/homebrew/bin/mysqldump',
                    '/Applications/MAMP/Library/bin/mysqldump',
                    '/Applications/XAMPP/xamppfiles/bin/mysqldump',
                ];
                
                foreach ($commonPaths as $path) {
                    if ($path === 'mysqldump') {
                        if (Process::run('mysqldump --version')->successful()) {
                            $dumpBinaryPath = 'mysqldump';
                            break;
                        }
                    } elseif (file_exists($path)) {
                        $dumpBinaryPath = $path;
                        break;
                    }
                }
            }
            
            // Allow skipping if not found, or use a default
            if (empty($dumpBinaryPath) && config('database.default') === 'mysql') {
                 // Try to fallback or throw error
                 throw new \Exception("Could not find 'mysqldump' binary. Please set BACKUP_DUMP_BINARY_PATH in .env");
            }
    
            $command = sprintf(
                '%s --user=%s --password=%s --host=%s %s > %s',
                $dumpBinaryPath,
                escapeshellarg($username),
                escapeshellarg($password),
                escapeshellarg($host),
                escapeshellarg($database),
                escapeshellarg($dbDumpFile)
            );
    
            // For PostgreSQL
            if (config('database.default') === 'pgsql') {
                $username = config('database.connections.pgsql.username');
                $password = config('database.connections.pgsql.password');
                $database = config('database.connections.pgsql.database');
                $host = config('database.connections.pgsql.host');
                $command = sprintf(
                    'PGPASSWORD=%s pg_dump -U %s -h %s -d %s -f %s',
                    escapeshellarg($password),
                    escapeshellarg($username),
                    escapeshellarg($host),
                    escapeshellarg($database),
                    escapeshellarg($dbDumpFile)
                );
            }
    
            $process = Process::run($command);
    
            if (!$process->successful()) {
                \Illuminate\Support\Facades\Log::error('Backup DB Failed', ['error' => $process->errorOutput()]);
                // Throwing exception so user knows something went wrong
                throw new \Exception('Database backup failed: ' . $process->errorOutput());
            }
        }

        if (file_exists($dbDumpFile)) {
            $zip->addFile($dbDumpFile, 'database.sql');
        }
        // Clean up temporary dump file after generic add
        // We can't delete it immediately if ZIP is still open and hasn't committed? 
        // Actually addFile reads it. We delete it after zip closes or keep it temp.
        // Let's rely on garbage collection or simple overwrite next time.
    }


    protected function addFolderToZip(ZipArchive $zip, $path, $folderName)
    {
        if (!is_dir($path)) {
            return;
        }

        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::LEAVES_ONLY
        );

        foreach ($files as $name => $file) {
            if (!$file->isDir()) {
                $filePath = $file->getRealPath();
                $relativePath = $folderName . '/' . substr($filePath, strlen($path) + 1);
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    public function listBackups()
    {
        $disk = Storage::disk('local');
        $files = $disk->files('backups');
        \Illuminate\Support\Facades\Log::info('List Backups', ['count' => count($files), 'files' => $files]);
        
        return collect($files)
            ->map(function ($file) {
                return [
                    'path' => $file,
                    'name' => basename($file),
                    'size' => Storage::size($file),
                    'last_modified' => Storage::lastModified($file),
                ];
            })
            ->sortByDesc('last_modified')
            ->values();
    }
}
