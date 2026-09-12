<?php

namespace App\Filament\Resources\TransactionResource\Pages;

use App\Filament\Resources\TransactionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTransactions extends ListRecords
{
    protected static string $resource = TransactionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => \Filament\Resources\Pages\ListRecords\Tab::make('All Transactions'),
            'pending' => \Filament\Resources\Pages\ListRecords\Tab::make('Pending')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending'))
                ->badge(fn () => \App\Models\Transaction::where('status', 'pending')->count())
                ->badgeColor('warning'),
            'fansku_pending' => \Filament\Resources\Pages\ListRecords\Tab::make('Fansku Pending')
                ->modifyQueryUsing(fn ($query) => $query->where('status', 'pending')->whereNotNull('fansku_support_id'))
                ->badge(fn () => \App\Models\Transaction::where('status', 'pending')->whereNotNull('fansku_support_id')->count())
                ->badgeColor('warning'),
            'donations' => \Filament\Resources\Pages\ListRecords\Tab::make('Donations')
                ->modifyQueryUsing(fn ($query) => $query->where('code', 'LIKE', 'KURON-PEDULI-%')),
            'subscriptions' => \Filament\Resources\Pages\ListRecords\Tab::make('Subscriptions')
                ->modifyQueryUsing(fn ($query) => $query->where('code', 'NOT LIKE', 'KURON-PEDULI-%')),
        ];
    }
}
