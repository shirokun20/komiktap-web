<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VoucherResource\Pages;
use App\Filament\Resources\VoucherResource\RelationManagers;
use App\Models\Voucher;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class VoucherResource extends Resource
{
    protected static ?string $model = Voucher::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Business';

    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Voucher Details')
                    ->schema([
                        Forms\Components\TextInput::make('code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->maxLength(255)
                            ->placeholder('e.g. SUMMER-SALE'),
                        Forms\Components\Select::make('type')
                            ->options([
                                'fixed' => 'Fixed Amount (IDR)',
                                'percentage' => 'Percentage (%)',
                            ])
                            ->required()
                            ->default('fixed')
                            ->native(false),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->label('Discount Value')
                            ->prefix(fn (Forms\Get $get) => $get('type') === 'fixed' ? 'IDR' : '%'),
                        Forms\Components\TextInput::make('usage_limit')
                            ->numeric()
                            ->label('Usage Limit')
                            ->helperText('Leave empty for unlimited usage.')
                            ->placeholder('e.g. 100'),
                        Forms\Components\Placeholder::make('usage_count')
                            ->label('Total Used')
                            ->content(fn (?Voucher $record): int => $record?->usage_count ?? 0),
                        Forms\Components\DateTimePicker::make('expires_at')
                            ->label('Expiration Date')
                            ->native(false),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->copyable()
                    ->weight('bold')
                    ->fontFamily('mono'),
                Tables\Columns\TextColumn::make('type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'fixed' => 'info',
                        'percentage' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('amount')
                    ->numeric() // Basic formatting, custom would be better
                    ->label('Value'),
                Tables\Columns\TextColumn::make('usage_count')
                    ->numeric()
                    ->sortable()
                    ->label('Used'),
                Tables\Columns\TextColumn::make('usage_limit')
                    ->numeric()
                    ->sortable()
                    ->placeholder('∞'),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->dateTime()
                    ->sortable()
                    ->color(fn ($record) => $record->expires_at?->isPast() ? 'danger' : 'success')
                    ->toggleable(isToggledHiddenByDefault: false),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\Filter::make('active')
                    ->query(fn (Builder $query): Builder => $query->where('is_active', true))
                    ->label('Active Only'),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVouchers::route('/'),
            'create' => Pages\CreateVoucher::route('/create'),
            'edit' => Pages\EditVoucher::route('/{record}/edit'),
        ];
    }
}
