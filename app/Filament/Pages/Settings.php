<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Settings\PricingSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

use App\Settings\ContactSettings;


class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'System';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.settings';

    // We hold the settings data in this array
    public ?array $data = [];

    public function mount(PricingSettings $settings, ContactSettings $contactSettings)
    {
        $this->form->fill([
            // existing pricing...
            'ketengan_base_price' => $settings->ketengan_base_price,
            'discount_3_months' => $settings->discount_3_months,
            'discount_6_months' => $settings->discount_6_months,
            'discount_12_months' => $settings->discount_12_months,
            'device_discount_percentage' => $settings->device_discount_percentage,
            
            // existing contact...
            'whatsapp_number' => $contactSettings->whatsapp_number,
            'whatsapp_description' => $contactSettings->whatsapp_description,
            'email_address' => $contactSettings->email_address,
            'email_description' => $contactSettings->email_description,
            'discord_url' => $contactSettings->discord_url,
            'discord_name' => $contactSettings->discord_name,

            'discord_description' => $contactSettings->discord_description,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Paket Ketengan Configuration')
                    ->description('Set base price and duration discounts')
                    ->schema([
                        // ... existing fields ...
                        TextInput::make('ketengan_base_price')
                            ->label('Base Price (Per Device/Month)')
                            ->required()
                            ->numeric()
                            ->prefix('IDR'),
                        TextInput::make('discount_3_months')
                            ->label('Discount (3 Months)')
                            ->required()
                            ->numeric()
                            ->step(0.01)
                            ->suffix('% (0.05 = 5%)'),
                        TextInput::make('discount_6_months')
                            ->label('Discount (6 Months)')
                            ->required()
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('discount_12_months')
                            ->label('Discount (12 Months)')
                            ->required()
                            ->numeric()
                            ->step(0.01),
                        TextInput::make('device_discount_percentage')
                            ->label('Device Discount (Per Added Device)')
                            ->helperText('Example: 0.02 means 2% discount per extra device')
                            ->required()
                            ->numeric()
                            ->step(0.001),
                    ])->columns(2),

                Section::make('Contact Information')
                    ->description('Manage contact details displayed on the website')
                    ->schema([
                         // ... existing contact fields ...
                        TextInput::make('whatsapp_number')->label('WhatsApp Number')->required(),
                        TextInput::make('whatsapp_description')->label('WhatsApp Description')->required(),
                        TextInput::make('email_address')->label('Email Address')->email()->required(),
                        TextInput::make('email_description')->label('Email Description')->required(),
                        TextInput::make('discord_name')->label('Discord Name')->required(),
                        TextInput::make('discord_url')->label('Discord Invite URL')->url()->required(),
                        TextInput::make('discord_description')->label('Discord Description')->required()->columnSpanFull(),
                    ])->columns(2),
            ])
            ->statePath('data');
    }

    public function save(PricingSettings $settings, ContactSettings $contactSettings)
    {
        $data = $this->form->getState();

        // Pricing
        $settings->ketengan_base_price = $data['ketengan_base_price'];
        $settings->discount_3_months = $data['discount_3_months'];
        $settings->discount_6_months = $data['discount_6_months'];
        $settings->discount_12_months = $data['discount_12_months'];
        $settings->device_discount_percentage = $data['device_discount_percentage'];
        $settings->save();

        // Contact
        $contactSettings->whatsapp_number = $data['whatsapp_number'];
        $contactSettings->whatsapp_description = $data['whatsapp_description'];
        $contactSettings->email_address = $data['email_address'];
        $contactSettings->email_description = $data['email_description'];
        $contactSettings->discord_url = $data['discord_url'];
        $contactSettings->discord_name = $data['discord_name'];
        $contactSettings->discord_description = $data['discord_description'];
        $contactSettings->save();



        Notification::make() 
            ->title('Saved successfully')
            ->success()
            ->send();
    }

    protected function getCachedFormActions(): array
    {
        return [
            $this->getSubmitFormAction(),
        ];
    }

    protected function getSubmitFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('save')
            ->label('Save changes')
            ->submit('save');
    }
}
