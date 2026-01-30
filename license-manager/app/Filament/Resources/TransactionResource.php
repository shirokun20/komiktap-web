<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Model;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Business';
    
    protected static ?int $navigationSort = 1;

    // Sort by latest by default
    protected static ?string $recordTitleAttribute = 'code';

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'customer_contact', 'plan_name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            'Contact' => $record->customer_contact,
            'Status' => $record->status,
        ];
    }

    // Sort by latest by default
    protected static ?string $defaultSort = 'created_at'; 

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Customer Info')
                    ->schema([
                        Forms\Components\TextInput::make('customer_contact')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('proof_digits')
                            ->maxLength(5),
                    ])->columns(2),

                Forms\Components\Section::make('Order Details')
                    ->schema([
                        Forms\Components\TextInput::make('plan_name')
                            ->required()
                            ->disabled() // Prevent changing plan after order
                            ->dehydrated(), 
                        Forms\Components\TextInput::make('device_quota')
                            ->required()
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('duration_months')
                            ->required()
                            ->numeric()
                            ->disabled(),
                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->disabled(),
                    ])->columns(2),

                Forms\Components\Section::make('Status & License')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->options([
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                            ])
                            ->required()
                            ->native(false),
                        Forms\Components\TextInput::make('license_id')
                            ->numeric()
                            ->disabled()
                            ->label('Linked License ID'),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Date'),
                Tables\Columns\TextColumn::make('code')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->weight('bold'),
                Tables\Columns\TextColumn::make('customer_contact'),
                Tables\Columns\TextColumn::make('plan_name')
                    ->searchable()
                    ->description(fn (Transaction $record): string => "{$record->device_quota} Devices • {$record->duration_months} Months"),
                Tables\Columns\TextColumn::make('amount')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('proof_digits')
                    ->searchable()
                    ->label('Proof'),
                Tables\Columns\TextColumn::make('license_id')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('invoice')
                        ->label('View Invoice')
                        ->icon('heroicon-o-document-text')
                        ->url(fn (Transaction $record) => route('invoices.show', $record))
                        ->openUrlInNewTab(),
                    Tables\Actions\Action::make('send_invoice')
                        ->label('Send Invoice')
                        ->icon('heroicon-o-paper-airplane')
                        ->requiresConfirmation()
                        ->action(function (Transaction $record, Tables\Actions\Action $action) {
                            $contact = $record->customer_contact;
                            $invoiceUrl = route('invoices.show', $record);
                            
                            // Check if it's an email
                            if (filter_var($contact, FILTER_VALIDATE_EMAIL)) {
                                \Illuminate\Support\Facades\Mail::to($contact)
                                    ->send(new \App\Mail\TransactionInvoice($record));
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Invoice Sent to Email')
                                    ->success()
                                    ->send();
                            } else {
                                // Assume it's a phone number (WA)
                                // Clean up number (remove non-digits)
                                $phone = preg_replace('/[^0-9]/', '', $contact);
                                
                                // Format logic: if starts with 0, replace with 62. If starts with 8, add 62.
                                if (str_starts_with($phone, '0')) {
                                    $phone = '62' . substr($phone, 1);
                                } elseif (str_starts_with($phone, '8')) {
                                    $phone = '62' . $phone;
                                }
                                
                                $message = urlencode("Hello! Here is your invoice for {$record->plan_name}: {$invoiceUrl}");
                                $waUrl = "https://wa.me/{$phone}?text={$message}";
                                
                                // Redirect to WA
                                // Since we are in an action closure, we can't easily "redirect" the browser using PHP return in a void action 
                                // without using specific Filament features or JS.
                                // Instead, we can notify the user to click a link, OR better yet, 
                                // we can make the Action itself a LinkAction if it's dynamic? No, it's a button.
                                
                                // Correct approach for Filament Actions to open URL:
                                $action->redirect($waUrl);
                                
                                \Filament\Notifications\Notification::make()
                                    ->title('Opening WhatsApp...')
                                    ->success()
                                    ->send();
                            }
                        }),
                ]),
                Tables\Actions\EditAction::make(),
                
                // Approve Action
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->modalHeading('Approve Transaction')
                    ->modalDescription('Are you sure? This will generate a License Key automatically.')
                    ->visible(fn (Transaction $record) => $record->status === 'pending')
                    ->action(function (Transaction $record) {
                        // 1. Generate License with "KURON" Pattern
                        // Format: KURON-XXXX-XXXX-XXXX
                        $random = strtoupper(\Illuminate\Support\Str::random(12));
                        $formatted = implode('-', str_split($random, 4));
                        $key = "KURON-{$formatted}"; // Example: KURON-A1B2-C3D4

                        $expiresAt = now()->addMonths($record->duration_months);
                        
                        $license = \App\Models\License::create([
                            'key' => $key,
                            'status' => 'active',
                            'max_devices' => $record->device_quota,
                            'expires_at' => $expiresAt,
                            'customer_contact' => $record->customer_contact,
                        ]);

                        // 2. Update Transaction
                        $record->update([
                            'status' => 'approved',
                            'license_id' => $license->id,
                        ]);

                        // 3. Notify
                        \Filament\Notifications\Notification::make()
                            ->title('Transaction Approved')
                            ->body("License generated: {$key}")
                            ->success()
                            ->send();
                    }),

                // Reject Action
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (Transaction $record) => $record->status === 'pending')
                    ->action(function (Transaction $record) {
                        $record->update(['status' => 'rejected']);
                        
                        \Filament\Notifications\Notification::make()
                            ->title('Transaction Rejected')
                            ->danger()
                            ->send();
                    }),
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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
