<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use App\Models\License;
use App\Models\ApkVersion;
use App\Models\Message;
use App\Models\Plan;
use App\Models\LicenseDevice;
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

        // Total Downloads
        $totalDownloads = ApkVersion::sum('download_count');

        // Pending Transactions
        $pendingTransactions = Transaction::where('status', 'pending')->count();

        // Active Licenses
        $activeLicenses = License::where('status', 'active')->count();

        // Used Devices
        $usedDevices = LicenseDevice::count();

        return [
            Stat::make('Total Downloads', number_format($totalDownloads, 0, ',', '.'))
                ->description('Total APK downloads')
                ->descriptionIcon('heroicon-m-arrow-down-tray')
                ->color('info')
                ->chart([5, 8, 12, 11, 15, 20, 25]),

            Stat::make('Active Licenses', number_format($activeLicenses, 0, ',', '.'))
                ->description('Currently active users')
                ->descriptionIcon('heroicon-m-users')
                ->color('primary')
                ->chart([2, 5, 8, 12, 10, 15, $activeLicenses]),

            Stat::make('Used Devices', number_format($usedDevices, 0, ',', '.'))
                ->description('Devices linked to licenses')
                ->descriptionIcon('heroicon-m-device-phone-mobile')
                ->color('success'),

            Stat::make('Total Revenue', 'IDR ' . number_format($totalRevenue, 0, ',', '.'))
                ->description('Total approved earnings')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart([7, 2, 10, 3, 15, 4, 17]),

            Stat::make('Pending Transactions', $pendingTransactions)
                ->description('Needs attention')
                ->descriptionIcon('heroicon-m-clock')
                ->color($pendingTransactions > 0 ? 'warning' : 'gray'),

            Stat::make('New Messages', Message::count())
                ->description('Support inquiries')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('warning'),
        ];
    }
}
