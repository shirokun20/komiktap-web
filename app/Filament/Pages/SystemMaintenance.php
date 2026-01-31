<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Services\BackupService;
use App\Services\AnomalyService;
use App\Models\MobileErrorReport;
use Illuminate\Support\Facades\Storage;
use Livewire\WithPagination;

class SystemMaintenance extends Page
{
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-wrench-screwdriver';
    protected static ?string $navigationLabel = 'System Maintenance';
    protected static ?string $title = 'System Maintenance & Backup';
    protected static ?string $navigationGroup = 'System';
    protected static ?int $navigationSort = 100;

    protected static string $view = 'filament.pages.system-maintenance';

    public $backupOptions = ['db', 'logs']; // Default checked
    public $anomalies = [];
    public $lastScanTime = null;

    // Mobile Error Report Filters
    public $searchQuery = '';

    public function mount()
    {
        // Initial load logic if needed
    }

    // --- BACKUP ACTIONS ---

    public function createBackup(BackupService $service)
    {
        try {
            if (empty($this->backupOptions)) {
                Notification::make()->title('Please select at least one item to backup.')
                    ->danger()->send();
                return;
            }

            $fileName = $service->createBackup($this->backupOptions);
            
            Notification::make()->title('Backup Created Successfully')
                ->success()->send();
            
            // Refresh list (handled by blade loop reading service directly or property)
            unset($this->backups); // Force property refresh if cached
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Backup Action Failed: ' . $e->getMessage());
            Notification::make()->title('Backup Failed: ' . $e->getMessage())
                ->danger()->send();
        }
    }

    public function downloadBackup($backupIds, BackupService $service)
    {
        // $backupIds is actually the filename here passed from blade wire:click
        $path = 'backups/' . $backupIds;
        if (Storage::exists($path)) {
            return Storage::download($path);
        }
        
        Notification::make()->title('File not found')->danger()->send();
    }

    public function deleteBackup($backupIds)
    {
        $path = 'backups/' . $backupIds;
        if (Storage::exists($path)) {
            Storage::delete($path);
            Notification::make()->title('Backup Deleted')->success()->send();
        }
    }

    public function getBackupsProperty()
    {
        return (new BackupService())->listBackups();
    }

    // --- ANOMALY ACTIONS ---

    public function scanAnomalies(AnomalyService $service)
    {
        $this->anomalies = $service->runChecks();
        $this->lastScanTime = now()->toDateTimeString();
        
        Notification::make()->title('Scan Completed')->success()->send();
    }

    // --- MOBILE ERROR REPORT ACTIONS ---

    public function getMobileErrorsProperty()
    {
        return MobileErrorReport::query()
            ->when($this->searchQuery, function($q) {
                $q->where('error_message', 'like', "%{$this->searchQuery}%")
                  ->orWhere('device_info', 'like', "%{$this->searchQuery}%");
            })
            ->latest()
            ->paginate(10);
    }

    public $stackTraceView = null; // Property to hold the stack trace

    public function openStackModal($id)
    {
        $report = MobileErrorReport::find($id);
        if ($report) {
            $this->stackTraceView = $report->stack_trace;
            $this->dispatch('open-modal', id: 'stack-trace'); 
        }
    }

    public function resolveError($id)
    {
        $report = MobileErrorReport::find($id);
        if ($report) {
            $report->update(['is_resolved' => true]);
            Notification::make()->title('Marked as Resolved')->success()->send();
        }
    }
    
    public function deleteError($id)
    {
        MobileErrorReport::destroy($id);
        Notification::make()->title('Report Deleted')->success()->send();
    }
}
