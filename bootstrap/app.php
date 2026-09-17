<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Semua error di path api/* berbentuk JSON envelope Kuron:
        // tanpa Accept: application/json pun tetap JSON (bukan 302 HTML),
        // 401/422 memakai {status: failed, data: {message}}.
        $kuron = function (string $message, int $code) {
            return response()->json([
                'app' => 'Kuron',
                'version' => config('app.version', '1.0.0'),
                'data' => ['message' => $message],
                'status' => 'failed',
            ], $code);
        };

        $isApi = fn ($request) => str_starts_with(ltrim((string) $request->path(), '/'), 'api/');

        $exceptions->render(function (\Illuminate\Auth\AuthenticationException $e, $request) use ($kuron, $isApi) {
            if ($isApi($request)) {
                return $kuron('Unauthenticated.', 401);
            }
        });

        $exceptions->render(function (\Illuminate\Validation\ValidationException $e, $request) use ($kuron, $isApi) {
            if ($isApi($request)) {
                return $kuron($e->getMessage() ?: 'Validation error', 422);
            }
        });

        $exceptions->shouldRenderJsonWhen(function ($request, $e) use ($isApi) {
            return $isApi($request);
        });
    })->create();
