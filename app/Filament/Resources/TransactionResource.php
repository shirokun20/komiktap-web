<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use App\Filament\Resources\CustomerResource; // Added
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
                            ->label('Proof Digits (Manual Payment)')
                            ->maxLength(5)
                            ->helperText('Only used for manual bank transfer verification. Not required when TriPay is enabled.'),
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
                        Forms\Components\TextInput::make('original_price')
                            ->label('Original Price (Before Discount)')
                            ->prefix('IDR')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(fn (?Transaction $record) => $record ? ($record->amount + ($record->discount_amount ?? 0)) : 0),

                        Forms\Components\TextInput::make('discount_amount')
                            ->label('Discount Amount')
                            ->numeric()
                            ->prefix('IDR')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?Transaction $record) => $record && $record->discount_amount > 0),

                        Forms\Components\TextInput::make('amount')
                            ->required()
                            ->numeric()
                            ->prefix('IDR')
                            ->disabled()
                            ->label('Final Paid Amount'), // Updated Label
                        
                        Forms\Components\TextInput::make('voucher_code')
                            ->disabled()
                            ->dehydrated(false)
                            ->visible(fn (?Transaction $record) => $record && !empty($record->voucher_code)),
                    ])->columns(2),

                Forms\Components\Section::make('Payment Information')
                    ->schema([
                        Forms\Components\TextInput::make('payment_method')
                            ->label('Method')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\Textarea::make('payment_details')
                            ->label('Destination / Notes')
                            ->disabled()
                            ->dehydrated(false)
                            ->columnSpanFull(),
                    ])->collapsible(),

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
                        Forms\Components\Placeholder::make('license_key')
                            ->label('License Key')
                            ->content(fn (Transaction $record): string => $record->license?->key ?? 'No License Generated'),
                    ])->columns(2),

                Forms\Components\Section::make('Fansku Information')
                    ->schema([
                        Forms\Components\TextInput::make('fansku_support_id')
                            ->label('Fansku Support ID')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—'),
                        Forms\Components\TextInput::make('fansku_code')
                            ->label('Fansku Code (INV-...)')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—'),
                        Forms\Components\TextInput::make('fansku_status')
                            ->label('Fansku Status')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—'),
                        Forms\Components\Placeholder::make('fansku_paid_at')
                            ->label('Paid At')
                            ->content(fn (Transaction $record): string => $record->fansku_paid_at
                                ? \Carbon\Carbon::parse($record->fansku_paid_at)->format('d M Y H:i')
                                : '—'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),

                Forms\Components\Section::make('TriPay Information')
                    ->schema([
                        Forms\Components\TextInput::make('tripay_reference')
                            ->label('TriPay Reference')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—'),
                        Forms\Components\TextInput::make('tripay_status')
                            ->label('TriPay Status')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—'),
                        Forms\Components\TextInput::make('tripay_payment_method')
                            ->label('TriPay Payment Method')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—'),
                        Forms\Components\TextInput::make('tripay_amount_received')
                            ->label('Amount Received')
                            ->prefix('IDR')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—'),
                        Forms\Components\TextInput::make('tripay_fee')
                            ->label('TriPay Fee')
                            ->prefix('IDR')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('—'),
                        Forms\Components\Placeholder::make('tripay_paid_at')
                            ->label('Paid At')
                            ->content(fn (Transaction $record): string => $record->tripay_paid_at
                                ? \Carbon\Carbon::parse($record->tripay_paid_at)->format('d M Y H:i')
                                : '—'),
                        Forms\Components\Placeholder::make('tripay_expired_at')
                            ->label('Expired At')
                            ->content(fn (Transaction $record): string => $record->tripay_expired_at
                                ? \Carbon\Carbon::parse($record->tripay_expired_at)->format('d M Y H:i')
                                : '—'),
                    ])
                    ->columns(2)
                    ->collapsible()
                    ->collapsed(),
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
                Tables\Columns\TextColumn::make('customer.contact')
                    ->label('Customer')
                    ->searchable()
                    ->sortable()
                    ->description(fn (Transaction $record): string => $record->customer?->name ?? 'New Customer')
                    ->action(
                        Tables\Actions\Action::make('view_customer')
                            ->url(fn (Transaction $record): ?string => $record->customer ? CustomerResource::getUrl('edit', ['record' => $record->customer]) : null)
                            ->openUrlInNewTab()
                    ),
                Tables\Columns\TextColumn::make('payment_method')
                    ->label('Payment Via')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                Tables\Columns\TextColumn::make('plan_name')
                    ->searchable()
                    ->description(fn (Transaction $record): string => "{$record->device_quota} Devices • {$record->duration_months} Months"),
                Tables\Columns\TextColumn::make('original_price')
                    ->label('Original Price')
                    ->money('IDR')
                    ->state(fn (Transaction $record) => $record->amount + ($record->discount_amount ?? 0))
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('discount_amount')
                    ->label('Discount')
                    ->money('IDR')
                    ->sortable()
                    ->placeholder('-')
                    ->color(fn ($state) => $state > 0 ? 'success' : 'gray'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Final Paid')
                    ->money('IDR')
                    ->weight('bold')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        'pending' => 'warning',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('fansku_support_id')
                    ->label('Fansku ID')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fansku_code')
                    ->label('Fansku Code')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fansku_status')
                    ->label('Fansku Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'paid'    => 'success',
                        'pending' => 'warning',
                        default   => 'gray',
                    })
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('fansku_paid_at')
                    ->label('Fansku Paid At')
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tripay_reference')
                    ->label('TriPay Ref')
                    ->searchable()
                    ->copyable()
                    ->fontFamily('mono')
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tripay_status')
                    ->label('TriPay Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'PAID'    => 'success',
                        'FAILED'  => 'danger',
                        'EXPIRED' => 'warning',
                        default   => 'gray',
                    })
                    ->placeholder('-')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('tripay_payment_method')
                    ->label('TriPay Method')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('tripay_paid_at')
                    ->label('Paid At')
                    ->dateTime()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('license/voucher')
                    ->label('Key / Voucher')
                    ->state(function (Transaction $record) {
                        $parts = [];
                        if ($record->voucher_code) $parts[] = "V: {$record->voucher_code}";
                        // if ($record->license) $parts[] = "L: ..."; // License is separate column usually or hidden
                        return implode(' | ', $parts); 
                    })
                    ->placeholder('-'),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),
                Tables\Filters\SelectFilter::make('fansku_status')
                    ->label('Fansku Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                    ]),
                Tables\Filters\Filter::make('fansku_only')
                    ->label('Fansku QRIS only')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('fansku_support_id')),
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
                        $license = app(\App\Services\TransactionApprovalService::class)->approve($record);

                        // Check if it is a Donation
                        if (str_starts_with($record->code, 'KURON-PEDULI')) {
                             \Filament\Notifications\Notification::make()
                                ->title('Donation Accepted')
                                ->body("Transaction marked as approved. No license generated.")
                                ->success()
                                ->send();
                             return;
                        }

                        \Filament\Notifications\Notification::make()
                            ->title('Transaction Approved')
                            ->body("License generated: {$license->key}")
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
                        app(\App\Services\TransactionApprovalService::class)->reject($record);

                        \Filament\Notifications\Notification::make()
                            ->title('Transaction Rejected')
                            ->danger()
                            ->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('bulk_approve')
                        ->label('Approve selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Approve selected transactions')
                        ->modalDescription('Only pending transactions will be approved. License keys are generated automatically for orders; donations get no license.')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $approved = 0;
                            $skipped = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                if ($record->status !== 'pending') {
                                    $skipped++;
                                    continue;
                                }

                                try {
                                    app(\App\Services\TransactionApprovalService::class)->approve($record);
                                    $approved++;
                                } catch (\Exception $e) {
                                    $failed++;
                                }
                            }

                            $notification = \Filament\Notifications\Notification::make()
                                ->title('Bulk approve completed')
                                ->body("{$approved} approved • {$skipped} skipped (not pending) • {$failed} failed");

                            $failed > 0 ? $notification->warning()->send() : $notification->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
                    Tables\Actions\BulkAction::make('bulk_reject')
                        ->label('Reject selected')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Reject selected transactions')
                        ->modalDescription('Only pending transactions will be rejected.')
                        ->action(function (\Illuminate\Database\Eloquent\Collection $records) {
                            $rejected = 0;
                            $skipped = 0;
                            $failed = 0;

                            foreach ($records as $record) {
                                if ($record->status !== 'pending') {
                                    $skipped++;
                                    continue;
                                }

                                try {
                                    app(\App\Services\TransactionApprovalService::class)->reject($record);
                                    $rejected++;
                                } catch (\Exception $e) {
                                    $failed++;
                                }
                            }

                            $notification = \Filament\Notifications\Notification::make()
                                ->title('Bulk reject completed')
                                ->body("{$rejected} rejected • {$skipped} skipped (not pending) • {$failed} failed");

                            $failed > 0 ? $notification->warning()->send() : $notification->success()->send();
                        })
                        ->deselectRecordsAfterCompletion(),
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
