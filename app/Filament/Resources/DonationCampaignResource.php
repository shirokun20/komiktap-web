<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DonationCampaignResource\Pages;
use App\Filament\Resources\DonationCampaignResource\RelationManagers;
use App\Models\DonationCampaign;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DonationCampaignResource extends Resource
{
    protected static ?string $model = DonationCampaign::class;

    protected static ?string $navigationIcon = 'heroicon-o-heart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->live(onBlur: true)
                    ->afterStateUpdated(fn (string $operation, $state, Forms\Set $set) => $operation === 'create' ? $set('slug', \Illuminate\Support\Str::slug($state)) : null),
                Forms\Components\TextInput::make('slug')
                    ->required()
                    ->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('target_amount')
                    ->numeric()
                    ->prefix('IDR')
                    ->default(0),
                
                Forms\Components\Placeholder::make('collected_funds')
                    ->label('Funds Collected')
                    ->content(function (DonationCampaign $record) {
                        // Calculate total approved transactions for this campaign
                        $total = \App\Models\Transaction::where('plan_name', $record->title)
                            ->where('status', 'approved')
                            ->sum('amount');
                        return 'IDR ' . number_format($total, 0, ',', '.');
                    })
                    ->visible(fn ($record) => $record !== null), // Only show on edit

                Forms\Components\Toggle::make('is_active')
                    ->required()
                    ->default(true),
                Forms\Components\FileUpload::make('image_path')
                    ->image()
                    ->directory('donation-campaigns')
                    ->columnSpanFull(),
                Forms\Components\MarkdownEditor::make('description')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')->disk('public'),
                Tables\Columns\TextColumn::make('title')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('target_amount')->money('IDR')->sortable(),
                Tables\Columns\ToggleColumn::make('is_active'),
                Tables\Columns\TextColumn::make('updated_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
            'index' => Pages\ListDonationCampaigns::route('/'),
            'create' => Pages\CreateDonationCampaign::route('/create'),
            'edit' => Pages\EditDonationCampaign::route('/{record}/edit'),
        ];
    }
}
