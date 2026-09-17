<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    /**
     * Send a success response.
     *
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    protected function success($data, int $code = 200): JsonResponse
    {
        return response()->json([
            'app' => 'Kuron',
            'version' => config('app.version', '1.0.0'),
            'data' => $data,
            'status' => 'success'
        ], $code);
    }

    /**
     * Send an error response.
     *
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function error(string $message, int $code = 400): JsonResponse
    {
        return response()->json([
            'app' => 'Kuron',
            'version' => config('app.version', '1.0.0'),
            'data' => ['message' => $message], // Standardize error message inside data or separate? User requested "data: [isi data]". For error, maybe data is null or contains error details. Let's put message in data for consistency with "data: [isi data]"
            'status' => 'failed' // user said "berhasil atau gagal", so 'success' or 'failed'
        ], $code);
    }

    /**
     * Send a validation error response.
     *
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function validationError($errors): JsonResponse
    {
        $message = 'Validation error';
        if ($errors instanceof \Illuminate\Support\MessageBag) {
            $message = $errors->first() ?: $message;
            $errors = $errors->messages();
        } elseif ($errors instanceof \Illuminate\Contracts\Support\MessageProvider) {
            $message = $errors->getMessageBag()->first() ?: $message;
            $errors = $errors->getMessageBag()->messages();
        }

        return response()->json([
            'app' => 'Kuron',
            'version' => config('app.version', '1.0.0'),
            'data' => ['message' => $message, 'errors' => $errors],
            'status' => 'failed',
        ], 422);
    }
}
