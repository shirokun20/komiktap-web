<?php

namespace App\Services;

use App\Models\License;
use App\Models\MobileErrorReport;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class AnomalyService
{
    public function runChecks(): array
    {
        $anomalies = [];

        // Check 1: Database Integrity - License without User
        $orphanLicenses = License::whereDoesntHave('user')->count();
        if ($orphanLicenses > 0) {
            $anomalies[] = [
                'severity' => 'warning',
                'title' => 'Orphan Licenses Detected',
                'description' => "Found {$orphanLicenses} licenses not linked to any valid user.",
            ];
        }

        // Check 2: Debug Mode in Production
        if (app()->environment('production') && config('app.debug')) {
            $anomalies[] = [
                'severity' => 'danger',
                'title' => 'Debug Mode Enabled in Production',
                'description' => 'APP_DEBUG is set to true. This reveals sensitive information.',
            ];
        }

        // Check 3: Laravel Log Errors
        $logPath = storage_path('logs/laravel.log');
        if (File::exists($logPath)) {
            $logContent = File::get($logPath);
            // Simple check for recent "CRITICAL" or "EMERGENCY" errors in last 10kb
            $recentLog = substr($logContent, -10000); 
            if (str_contains($recentLog, '.CRITICAL:') || str_contains($recentLog, '.EMERGENCY:')) {
                $anomalies[] = [
                    'severity' => 'danger',
                    'title' => 'Critical Errors in Log',
                    'description' => 'Found recent CRITICAL errors in laravel.log. Check logs immediately.',
                ];
            }
        }

        // Check 4: High Volume of Mobile Errors
        $recentErrors = MobileErrorReport::where('created_at', '>=', now()->subHour())->count();
        if ($recentErrors > 50) {
            $anomalies[] = [
                'severity' => 'danger',
                'title' => 'High Volume of Mobile Errors',
                'description' => "Detected {$recentErrors} mobile error reports in the last hour. Potential app crash loop.",
            ];
        }

        if (empty($anomalies)) {
             $anomalies[] = [
                'severity' => 'success',
                'title' => 'System Healthy',
                'description' => 'No known anomalies detected at this time.',
            ];
        }

        return $anomalies;
    }
}
