<?php

namespace App\Filament\Resources\ApkVersionResource\Pages;

use App\Filament\Resources\ApkVersionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditApkVersion extends EditRecord
{
    protected static string $resource = ApkVersionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
