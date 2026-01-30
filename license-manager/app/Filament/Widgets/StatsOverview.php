<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\License;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Calculate Total Revenue (only approved transactions)
        $totalRevenue = Transaction::where('status', 'approved')->sum('amount');
        
        // Calculate Today's Revenue
        $todayRevenue = Transaction::where('status', 'approved')
            ->whereDate('created_at', today())
            ->sum('amount');

        // Pending Transactions
        $pendingTransactions = Transaction::where('status', 'pending')->count();

        // Active Licenses
        $activeLicenses = License::where('status', 'active')->count();

        return [
            Stat::make('Total Revenue', 'IDR ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Total approved earnings')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]), // Mock chart for visual appeal

            Stat::make('Today\'s Revenue', 'IDR ' . number_format($todayRevenue, 0, ',', '.'))
                ->description('Earnings from today')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Pending Transactions', $pendingTransactions)
                ->description('Needs attention')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingTransactions > 0 ? 'warning' : 'gray'),

            Stat::make('Active Licenses', $activeLicenses)
                ->description('Currently active users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary'),
        ];
    }
}
