<?php

namespace App\Filament\Pages;

use App\Services\FanskuService;
use App\Settings\PaymentGatewaySettings;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Log;

class PaymentConfiguration extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $title = 'Payment Configuration';

    protected static string $view = 'filament.pages.payment-configuration';

    public ?array $data = [];

    public function mount(PaymentGatewaySettings $settings)
    {
        $this->form->fill([
            'fansku_enabled' => $settings->fansku_enabled,
            'manual_enabled' => $settings->manual_enabled,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('QRIS Otomatis (Fansku)')
                    ->description('Payment gateway utama untuk semua transaksi (lisensi + donasi) via QRIS otomatis. Metode manual DB disembunyikan sementara.')
                    ->schema([
                        Toggle::make('fansku_enabled')
                            ->label('Aktifkan QRIS Otomatis')
                            ->helperText('Saat aktif, checkout membuat QRIS via Fansku dan metode manual disembunyikan.')
                            ->columnSpanFull(),
                        Placeholder::make('fansku_status')
                            ->label('Status Koneksi Fansku')
                            ->content(function (): string {
                                if (! config('fansku.api_key')) {
                                    return 'API key belum diisi di .env (FANSKU_API_KEY).';
                                }

                                try {
                                    $active = app(FanskuService::class)->isQrisActive();

                                    return $active
                                        ? 'Terhubung — QRIS aktif.'
                                        : 'Terhubung — QRIS NONAKTIF di dashboard Fansku (checkout fallback manual).';
                                } catch (\Exception $e) {
                                    Log::warning('PaymentConfiguration: Fansku status check failed', [
                                        'error' => $e->getMessage(),
                                    ]);

                                    return 'Tidak dapat menghubungi Fansku API — cek key/jaringan.';
                                }
                            })
                            ->columnSpanFull(),
                    ]),

                Section::make('Manual (Cadangan)')
                    ->description('Metode transfer manual dari database. Hanya dipakai saat QRIS otomatis mati atau gagal.')
                    ->schema([
                        Toggle::make('manual_enabled')
                            ->label('Aktifkan Metode Manual')
                            ->helperText('Saat mati, daftar metode manual disembunyikan dari halaman bayar.')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function save(PaymentGatewaySettings $settings)
    {
        $data = $this->form->getState();

        $settings->fansku_enabled = (bool) ($data['fansku_enabled'] ?? false);
        $settings->manual_enabled = (bool) ($data['manual_enabled'] ?? false);
        $settings->save();

        Notification::make()
            ->title('Pengaturan pembayaran tersimpan.')
            ->body('QRIS Otomatis: ' . ($settings->fansku_enabled ? 'aktif' : 'nonaktif') . ' • Manual: ' . ($settings->manual_enabled ? 'aktif' : 'nonaktif'))
            ->success()
            ->send();
    }
}
