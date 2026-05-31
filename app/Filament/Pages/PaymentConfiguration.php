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
use Filament\Forms\Components\Select;
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
                                Select::make('usage_type')
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
                                    ->label('QRIS Image (Static QRIS Header/Image)')
                                    ->image()
                                    ->disk('public')
                                    ->visibility('public')
                                    ->directory('payment-qris')
                                    ->columnSpanFull(),
                                TextInput::make('qris_string')
                                    ->label('QRIS String (Dynamic QRIS Data)')
                                    ->placeholder('Masukkan string data QRIS statis untuk dirender dinamis (Opsional)')
                                    ->columnSpanFull(),
                            ])
                            ->columnSpanFull()
                            ->reorderableWithButtons()
                            ->itemLabel(fn (array $state): ?string => ($state['name'] ?? 'New Method') . ' - ' . ucfirst($state['usage_type'] ?? 'All')),
                    ])->columns(2),

                Section::make('TriPay Integration')
                    ->description('Configure TriPay payment gateway for automatic payment processing. Get credentials from tripay.co.id/member/merchant.')
                    ->schema([
                        Toggle::make('tripay_is_enabled')
                            ->label('Enable TriPay Gateway')
                            ->helperText('When enabled, checkout will create a TriPay transaction automatically.')
                            ->columnSpanFull()
                            ->afterStateHydrated(fn ($component) => $component->state((bool) config('tripay.is_enabled'))),
                        TextInput::make('tripay_api_key')
                            ->label('API Key')
                            ->password()
                            ->revealable()
                            ->placeholder('DEV-...')
                            ->afterStateHydrated(fn ($component) => $component->state(config('tripay.api_key'))),
                        TextInput::make('tripay_private_key')
                            ->label('Private Key')
                            ->password()
                            ->revealable()
                            ->afterStateHydrated(fn ($component) => $component->state(config('tripay.private_key'))),
                        TextInput::make('tripay_merchant_code')
                            ->label('Merchant Code')
                            ->placeholder('T...')
                            ->afterStateHydrated(fn ($component) => $component->state(config('tripay.merchant_code'))),
                        Select::make('tripay_mode')
                            ->label('Mode')
                            ->options([
                                'sandbox'    => 'Sandbox (Testing)',
                                'production' => 'Production (Live)',
                            ])
                            ->default('sandbox')
                            ->afterStateHydrated(fn ($component) => $component->state(config('tripay.mode', 'sandbox'))),
                    ])
                    ->columns(2)
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function save(PaymentSettings $settings)
    {
        $data = $this->form->getState();

        $settings->is_enabled = $data['is_enabled'];
        $settings->payment_methods = $data['payment_methods'] ?? [];
        $settings->save();

        // Note: TriPay credentials are stored in .env, not in DB settings.
        // Display a reminder to update .env for TriPay config.
        Notification::make() 
            ->title('Settings saved successfully.')
            ->body('Remember to update TRIPAY_* variables in your .env file for TriPay credentials.')
            ->success()
            ->send();
    }
}
