<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Settings\PricingSettings;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;

class Settings extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cog';

    protected static string $view = 'filament.pages.settings';

    // We hold the settings data in this array
    public ?array $data = [];

    public function mount(PricingSettings $settings)
    {
        $this->form->fill([
            'ketengan_base_price' => $settings->ketengan_base_price,
            'discount_3_months' => $settings->discount_3_months,
            'discount_6_months' => $settings->discount_6_months,
            'discount_12_months' => $settings->discount_12_months,
            'device_discount_percentage' => $settings->device_discount_percentage,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Paket Ketengan Configuration')
                    ->description('Set base price and duration discounts')
                    ->schema([
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
            ])
            ->statePath('data');
    }

    public function save(PricingSettings $settings)
    {
        $data = $this->form->getState();

        $settings->ketengan_base_price = $data['ketengan_base_price'];
        $settings->discount_3_months = $data['discount_3_months'];
        $settings->discount_6_months = $data['discount_6_months'];
        $settings->discount_12_months = $data['discount_12_months'];
        $settings->device_discount_percentage = $data['device_discount_percentage'];
        
        $settings->save();

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
