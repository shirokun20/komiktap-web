<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ActivityLogResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Spatie\Activitylog\Models\Activity;
use Illuminate\Database\Eloquent\Model;

class ActivityLogResource extends Resource
{
    protected static ?string $model = Activity::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationLabel = 'Audit Logs';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Activity Details')
                            ->schema([
                                Forms\Components\TextInput::make('log_name')
                                    ->label('Log Type')
                                    ->formatStateUsing(fn ($state) => ucwords($state))
                                    ->readonly(),
                                Forms\Components\TextInput::make('description')
                                    ->label('Action')
                                    ->formatStateUsing(fn ($state) => ucfirst($state))
                                    ->readonly(),
                                Forms\Components\Placeholder::make('created_at')
                                    ->label('Timestamp')
                                    ->content(fn ($record) => $record->created_at->format('d M Y, H:i:s')),
                                Forms\Components\Placeholder::make('causer')
                                    ->label('Performed By')
                                    ->content(fn ($record) => $record->causer ? $record->causer->name : 'System'),
                            ])
                            ->columns(2),

                        Forms\Components\Section::make('Subject Information')
                            ->schema([
                                Forms\Components\TextInput::make('subject_type')
                                    ->label('Module')
                                    ->formatStateUsing(fn ($state) => class_basename($state))
                                    ->readonly(),
                                Forms\Components\Placeholder::make('subject_detail')
                                    ->label('Identifier')
                                    ->content(function ($record) {
                                        if (!$record->subject) {
                                            return "ID: {$record->subject_id} (Deleted or Unknown)";
                                        }
                                        
                                        return match (class_basename($record->subject_type)) {
                                            'Transaction' => "Code: {$record->subject->code}",
                                            'License' => "Key: {$record->subject->key}",
                                            'User' => "Name: {$record->subject->name}",
                                            default => "ID: {$record->subject_id}",
                                        };
                                    }),
                            ])
                            ->columns(2),
                    ])
                    ->columnSpan(['lg' => 2]),

                Forms\Components\Group::make()
                    ->schema([
                        Forms\Components\Section::make('Changes')
                            ->schema([
                                Forms\Components\KeyValue::make('properties.attributes')
                                    ->label('New Values')
                                    ->disabled(),
                                Forms\Components\KeyValue::make('properties.old')
                                    ->label('Old Values')
                                    ->disabled(),
                            ])
                            ->visible(fn ($record) => $record->properties && ($record->properties['attributes'] ?? null))
                    ])
                    ->columnSpan(['lg' => 1]),
            ])
            ->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Time')
                    ->dateTime('M d, Y H:i:s')
                    ->sortable(),
                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'created' => 'success',
                        'updated' => 'warning',
                        'deleted' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('subject_type')
                    ->label('Subject')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->searchable(),
                Tables\Columns\TextColumn::make('subject_id')
                    ->label('ID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('causer.name')
                    ->label('User')
                    ->default('System'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListActivityLogs::route('/'),
        ];
    }
}
