<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApkVersionResource\Pages;
use App\Filament\Resources\ApkVersionResource\RelationManagers;
use App\Models\ApkVersion;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ApkVersionResource extends Resource
{
    protected static ?string $model = ApkVersion::class;

    protected static ?string $navigationGroup = 'System';
    protected static ?string $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('APK Details')
                    ->schema([
                        Forms\Components\TextInput::make('version_code')
                            ->required()
                            ->label('Version Code')
                            ->placeholder('e.g. 1.0.0'),
                        Forms\Components\TextInput::make('version_name')
                            ->required()
                            ->label('Version Name')
                            ->placeholder('e.g. Initial Release'),
                        Forms\Components\FileUpload::make('file_path')
                            ->required()
                            ->label('APK File')
                            ->directory('apks')
                            ->preserveFilenames()
                            ->acceptedFileTypes(['application/vnd.android.package-archive'])
                            ->maxSize(512000)
                            ->columnSpanFull(),
                        Forms\Components\TextInput::make('min_android_version')
                            ->label('Compatibility')
                            ->placeholder('e.g. Android 5.0+')
                            ->required(),
                        Forms\Components\MarkdownEditor::make('changelog')
                            ->label('Changelog')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active Version')
                            ->default(true)
                            ->required(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('version_name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('version_code')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('download_count')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListApkVersions::route('/'),
            'create' => Pages\CreateApkVersion::route('/create'),
            'edit' => Pages\EditApkVersion::route('/{record}/edit'),
        ];
    }
}
