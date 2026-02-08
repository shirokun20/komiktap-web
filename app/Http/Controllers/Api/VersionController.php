<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApkVersion;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Storage;

class VersionController extends Controller
{
    use ApiResponse;

    public function check(Request $request)
    {
        $request->validate([
            'version_code' => 'nullable|integer',
        ]);

        $currentVersionCode = $request->input('version_code', 0);

        $latestVersion = ApkVersion::where('is_active', true)
            ->orderBy('version_code', 'desc')
            ->first();

        if (!$latestVersion) {
            return $this->success([
                'update_available' => false,
                'force_update' => false,
                'message' => 'No active version found.',
            ]);
        }

        $updateAvailable = $latestVersion->version_code > $currentVersionCode;
        
        // Determine force update logic. 
        // Since we don't have a specific 'min_supported_version' field yet, 
        // we'll default to false or we could use a specific flag if it existed.
        // For now, let's assume false.
        $forceUpdate = false; 

        return $this->success([
            'update_available' => $updateAvailable,
            'force_update' => $forceUpdate,
            'latest_version' => [
                'code' => $latestVersion->version_code,
                'name' => $latestVersion->version_name,
                'changelog' => $latestVersion->changelog,
                'download_url' => $latestVersion->file_path ? Storage::url($latestVersion->file_path) : null,
                'min_android_version' => $latestVersion->min_android_version,
                'published_at' => $latestVersion->created_at,
            ]
        ]);
    }
}
