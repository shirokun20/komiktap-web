<?php

namespace App\Filament\Resources\LicenseResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DevicesRelationManager extends RelationManager
{
    protected static string $relationship = 'devices';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('device_id')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('device_name')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('device_name')
            ->columns([
                Tables\Columns\TextColumn::make('device_id')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('device_name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('last_seen')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                // Usually devices are added via API, allowing create here might be useful for testing but mostly read-only
                Tables\Actions\CreateAction::make()
                    ->before(function ($action, RelationManager $livewire) {
                        $license = $livewire->getOwnerRecord();
                        if ($license->devices()->count() >= $license->max_devices) {
                            \Filament\Notifications\Notification::make()
                                ->title('Max devices reached')
                                ->body("This license allows a maximum of {$license->max_devices} devices.")
                                ->danger()
                                ->send();
                            
                            $action->halt();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
