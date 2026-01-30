<?php

namespace App\Filament\Widgets;

use App\Models\Transaction;
use Filament\Widgets\ChartWidget;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Revenue (Last 30 Days)';
    
    protected static ?int $sort = 2; // Display after stats
    
    protected int | string | array $columnSpan = 'full';

    protected function getData(): array
    {
        // Manual aggregation to avoid requiring flowframe/laravel-trend
        $data = Transaction::where('status', 'approved')
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->selectRaw('DATE(created_at) as date, SUM(amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->pluck('total', 'date')
            ->toArray();

        $labels = [];
        $values = [];
        
        // Generate last 30 days
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $labels[] = $date;
            $values[] = $data[$date] ?? 0;
        }
            
        return [
            'datasets' => [
                [
                    'label' => 'Revenue',
                    'data' => $values,
                    'fill' => 'start',
                    'borderColor' => '#10b981', // Emerald 500
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
