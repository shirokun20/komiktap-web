<?php

namespace Tests\Feature;

use App\Filament\Pages\SystemMaintenance;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class BackupSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create(['email' => 'admin@komiktap.com']));
        Storage::fake('local');
    }

    public function test_known_backup_can_be_downloaded(): void
    {
        Storage::disk('local')->put('backups/a.zip', 'fake-zip-bytes');

        Livewire::test(SystemMaintenance::class)
            ->call('downloadBackup', 'a.zip')
            ->assertFileDownloaded('a.zip');
    }

    public function test_unknown_backup_name_is_not_downloaded(): void
    {
        Storage::disk('local')->put('backups/a.zip', 'fake-zip-bytes');

        Livewire::test(SystemMaintenance::class)
            ->call('downloadBackup', '../../.env')
            ->assertNoFileDownloaded();
    }

    public function test_unknown_name_is_not_deleted(): void
    {
        Storage::disk('local')->put('backups/a.zip', 'fake-zip-bytes');
        Storage::disk('local')->put('victim.txt', 'do-not-touch');

        Livewire::test(SystemMaintenance::class)
            ->call('deleteBackup', '../victim.txt');

        $this->assertTrue(Storage::disk('local')->exists('victim.txt'));
        $this->assertTrue(Storage::disk('local')->exists('backups/a.zip'));
    }

    public function test_known_backup_can_be_deleted(): void
    {
        Storage::disk('local')->put('backups/a.zip', 'fake-zip-bytes');

        Livewire::test(SystemMaintenance::class)
            ->call('deleteBackup', 'a.zip');

        $this->assertFalse(Storage::disk('local')->exists('backups/a.zip'));
    }

    public function test_temp_dump_removed_after_successful_backup(): void
    {
        Storage::fake('local');

        $realDb = tempnam(sys_get_temp_dir(), 'komiktap-test-db');
        file_put_contents($realDb, 'fake-sqlite-bytes');
        config([
            'database.default' => 'sqlite',
            'database.connections.sqlite.database' => $realDb,
        ]);

        try {
            $fileName = (new \App\Services\BackupService)->createBackup(['db']);
        } finally {
            @unlink($realDb);
        }

        $this->assertTrue(Storage::disk('local')->exists("backups/{$fileName}"));
        $this->assertFalse(Storage::disk('local')->exists('backups/db-dump.sql'));
    }

    public function test_temp_dump_removed_after_failed_backup(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('backups/db-dump.sql', 'stale-leftover');

        config(['database.default' => 'mysql']);

        try {
            (new \App\Services\BackupService)->createBackup(['db']);
            $this->fail('Expected backup exception was not thrown.');
        } catch (\Exception $e) {
            $this->assertFalse(Storage::disk('local')->exists('backups/db-dump.sql'));
        } finally {
            // RefreshDatabase teardown butuh koneksi awal.
            config(['database.default' => 'sqlite']);
        }
    }
}
