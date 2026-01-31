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
            'donations' => \Filament\Resources\Pages\ListRecords\Tab::make('Donations')
                ->modifyQueryUsing(fn ($query) => $query->where('code', 'LIKE', 'KURON-PEDULI-%')),
            'subscriptions' => \Filament\Resources\Pages\ListRecords\Tab::make('Subscriptions')
                ->modifyQueryUsing(fn ($query) => $query->where('code', 'NOT LIKE', 'KURON-PEDULI-%')),
        ];
    }
}
