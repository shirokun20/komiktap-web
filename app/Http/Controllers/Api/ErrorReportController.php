<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MobileErrorReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Traits\ApiResponse;

class ErrorReportController extends Controller
{
    use ApiResponse;

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'error_message' => 'required|string',
            'stack_trace' => 'nullable|string',
            'device_info' => 'nullable|string',
            'app_version' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        $report = MobileErrorReport::create([
            'user_id' => $request->user()?->id,
            'error_message' => $request->error_message,
            'stack_trace' => $request->stack_trace,
            'device_info' => $request->device_info,
            'app_version' => $request->app_version,
        ]);

        return $this->success([
            'message' => 'Error report submitted successfully',
            'report_id' => $report->id
        ], 201);
    }
}
