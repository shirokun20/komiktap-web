<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Settings\PaymentSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class PaymentConfiguration extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'System';
    
    protected static ?string $title = 'Payment Configuration';

    protected static string $view = 'filament.pages.payment-configuration';
    
    public ?array $data = [];

    public function mount(PaymentSettings $settings)
    {
        $this->form->fill([
            'is_enabled' => $settings->is_enabled,
            'payment_methods' => $settings->payment_methods,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Payment Methods')
                    ->description('Configure available payment methods for the checkout modal.')
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label('Enable Payment System')
                            ->columnSpanFull(),
                        
                        Repeater::make('payment_methods')
                            ->label('Methods List')
                            ->schema([
                                TextInput::make('name')
                                    ->label('Method Name')
                                    ->placeholder('e.g. BCA, QRIS, GoPay')
                                    ->required(),
                                TextInput::make('account_number')
                                    ->label('Account Number / VA'),
                                TextInput::make('account_holder')
                                    ->label('Account Holder Name'),
                                \Filament\Forms\Components\Select::make('usage_type')
                                    ->label('Usage')
                                    ->options([
                                        'all' => 'All (Order & Donation)',
                                        'order' => 'Order Only',
                                        'donation' => 'Donation Only',
                                    ])
                                    ->default('all')
                                    ->required(),
                                Textarea::make('instructions')
                                    ->label('Payment Instructions')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                FileUpload::make('qris_image_path')
                                    ->label('QRIS Image (Optional)')
                                    ->image()
                                    ->disk('public')
                                    ->visibility('public')
                                    ->directory('payment-qris')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull()
                            ->reorderableWithButtons()
                            ->itemLabel(fn (array $state): ?string => ($state['name'] ?? 'New Method') . ' - ' . ucfirst($state['usage_type'] ?? 'All')),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(PaymentSettings $settings)
    {
        $data = $this->form->getState();

        $settings->is_enabled = $data['is_enabled'];
        $settings->payment_methods = $data['payment_methods'] ?? [];
        $settings->save();

        Notification::make() 
            ->title('Settings saved successfully.')
            ->success()
            ->send();
    }
}
