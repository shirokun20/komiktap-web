<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\License;
use App\Models\LicenseDevice;
use App\Traits\ApiResponse;
use Carbon\Carbon;

class LicenseController extends Controller
{
    use ApiResponse;

    public function check(Request $request)
    {
        $validator = \Illuminate\Support\Facades\Validator::make($request->all(), [
            'license_key' => 'required|string',
            'device_id' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $key = $request->license_key;
        $deviceId = $request->device_id;
        $deviceName = $request->device_name;

        // 1. Find License
        $license = License::where('key', $key)->first();

        if (!$license) {
            return $this->error('Invalid license key', 404);
        }

        // 2. Check Status
        if ($license->status !== 'active') {
             return $this->error('License is ' . $license->status, 403);
        }

        // 3. Check Expiration
        if ($license->expires_at && $license->expires_at->isPast()) {
            return $this->error('License expired on ' . $license->expires_at->format('Y-m-d'), 403);
        }

        // 4. Device Check
        $existingDevice = $license->devices()->where('device_id', $deviceId)->first();

        if ($existingDevice) {
            // Update last seen
            $existingDevice->update([
                'last_seen' => now(),
                'device_name' => $deviceName ?? $existingDevice->device_name // Update name if provided
            ]);
        } else {
            // Check slots
            $currentCount = $license->devices()->count();
            if ($currentCount >= $license->max_devices) {
                return $this->error('Max devices reached (' . $license->max_devices . ')', 403);
            }

            // Register new device
            $license->devices()->create([
                'device_id' => $deviceId,
                'device_name' => $deviceName,
                'last_seen' => now()
            ]);
        }

        // 5. Get Plan Name
        $transaction = $license->transactions()
            ->where('status', 'approved')
            ->latest()
            ->first();

        return $this->success([
            'valid' => true,
            'expires_at' => $license->expires_at ? $license->expires_at->toDateTimeString() : null,
            'max_devices' => $license->max_devices,
            'used_devices' => $license->devices()->count(),
            'plan_name' => $transaction ? $transaction->plan_name : null,
            'message' => 'License active'
        ]);
    }
}
