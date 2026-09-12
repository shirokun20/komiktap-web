<?php

namespace App\Filament\Widgets;

use App\Services\FanskuService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Log;

class FanskuBalance extends BaseWidget
{
    protected static ?int $sort = 0;

    protected static ?string $pollingInterval = '300s';

    protected function getStats(): array
    {
        if (! config('fansku.is_enabled', false)) {
            return [
                Stat::make('Fansku Balance', 'Disabled')
                    ->description('Set FANSKU_IS_ENABLED=true to activate')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('gray'),
            ];
        }

        try {
            $fansku = app(FanskuService::class);
            $balance = $fansku->getBalance();
            $count = $fansku->getSupportsCount();

            return [
                Stat::make(
                    'Fansku Available',
                    'IDR ' . number_format($balance['available_balance'] ?? 0, 0, ',', '.')
                )
                    ->description('Saldo dapat ditarik')
                    ->descriptionIcon('heroicon-m-banknotes')
                    ->color('success'),

                Stat::make(
                    'Fansku Pending',
                    'IDR ' . number_format($balance['pending_balance'] ?? 0, 0, ',', '.')
                )
                    ->description('Saldo belum settle')
                    ->descriptionIcon('heroicon-m-clock')
                    ->color('warning'),

                Stat::make(
                    'Fansku Supports',
                    number_format($count['total_supports'] ?? 0, 0, ',', '.')
                )
                    ->description('IDR ' . number_format($count['total_creator_earning'] ?? 0, 0, ',', '.') . ' creator earning')
                    ->descriptionIcon('heroicon-m-heart')
                    ->color('primary'),
            ];
        } catch (\Exception $e) {
            Log::warning('FanskuBalance widget failed', ['error' => $e->getMessage()]);

            return [
                Stat::make('Fansku Balance', 'Unavailable')
                    ->description('Fansku API error — check logs')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('danger'),
            ];
        }
    }
}
