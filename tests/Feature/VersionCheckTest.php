<?php

namespace Tests\Feature;

use App\Models\ApkVersion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VersionCheckTest extends TestCase
{
    use RefreshDatabase;

    public function test_check_version_returns_no_update_found_when_no_active_version()
    {
        $response = $this->getJson('/api/check-update?version_code=1');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'update_available' => false,
                    'message' => 'No active version found.',
                ]
            ]);
    }

    public function test_check_version_returns_update_available_when_newer_version_exists()
    {
        // Create an older version
        ApkVersion::create([
            'version_code' => 1,
            'version_name' => '1.0.0',
            'file_path' => 'apks/v1.apk',
            'min_android_version' => 'Android 5.0',
            'is_active' => true,
        ]);

        // Create a newer version
        $latest = ApkVersion::create([
            'version_code' => 2,
            'version_name' => '1.1.0',
            'file_path' => 'apks/v2.apk',
            'min_android_version' => 'Android 5.0',
            'changelog' => 'Fix bugs',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/check-update?version_code=1');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'update_available' => true,
                    'latest_version' => [
                        'code' => 2,
                        'name' => '1.1.0',
                        'changelog' => 'Fix bugs',
                    ]
                ]
            ]);
    }

    public function test_check_version_returns_no_update_available_when_current_is_latest()
    {
        ApkVersion::create([
            'version_code' => 2,
            'version_name' => '1.1.0',
            'file_path' => 'apks/v2.apk',
            'min_android_version' => 'Android 5.0',
            'is_active' => true,
        ]);

        $response = $this->getJson('/api/check-update?version_code=2');

        $response->assertStatus(200)
            ->assertJson([
                'status' => 'success',
                'data' => [
                    'update_available' => false,
                    'latest_version' => [
                        'code' => 2,
                    ]
                ]
            ]);
    }
}
