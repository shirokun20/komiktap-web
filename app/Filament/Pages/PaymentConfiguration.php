<?php

namespace App\Filament\Pages;

use App\Services\FanskuService;
use App\Settings\PaymentGatewaySettings;
use App\Settings\PaymentSettings;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

    public function mount(PaymentGatewaySettings $settings, PaymentSettings $paymentSettings)
    {
        $methods = collect($paymentSettings->payment_methods ?? [])
            ->map(function ($method) {
                // Backward compatibility: methods saved before is_active existed count as active.
                if (! array_key_exists('is_active', $method)) {
                    $method['is_active'] = true;
                }

                return $method;
            })
            ->values()
            ->all();

        $this->form->fill([
            'is_enabled' => $paymentSettings->is_enabled,
            'fansku_enabled' => $settings->fansku_enabled,
            'manual_enabled' => $settings->manual_enabled,
            'payment_methods' => $methods,
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Status Umum')
                    ->description('Saklar utama sistem pembayaran di halaman checkout.')
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label('Aktifkan Sistem Pembayaran')
                            ->helperText('Saat mati, halaman bayar menampilkan status nonaktif untuk semua gateway.')
                            ->columnSpanFull(),
                    ]),

                Section::make('QRIS Otomatis (Fansku)')
                    ->description('Payment gateway otomatis via QRIS. Bisa tayang berdampingan dengan metode manual.')
                    ->schema([
                        Toggle::make('fansku_enabled')
                            ->label('Aktifkan QRIS Otomatis')
                            ->helperText('Saat aktif, opsi "QRIS Otomatis" muncul di checkout dan membuat QR via Fansku.')
                            ->live()
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

                Section::make('Metode Manual (Transfer / E-Wallet)')
                    ->description('Daftar rekening & QRIS manual. Atur on/off per metode — hanya yang aktif yang tampil di checkout.')
                    ->schema([
                        Toggle::make('manual_enabled')
                            ->label('Aktifkan Metode Manual')
                            ->helperText('Saklar utama: saat mati, seluruh daftar manual disembunyikan dari halaman bayar.')
                            ->live()
                            ->columnSpanFull(),
                        Repeater::make('payment_methods')
                            ->label('Daftar Metode Manual')
                            ->itemLabel(fn (array $state): ?string => ($state['name'] ?? null) ?: 'Metode baru')
                            ->schema([
                                Toggle::make('is_active')
                                    ->label('Aktif')
                                    ->default(true)
                                    ->inline(false),
                                TextInput::make('name')
                                    ->label('Nama Metode')
                                    ->placeholder('cth: BCA Transfer, DANA, QRIS Manual')
                                    ->required(),
                                Select::make('usage_type')
                                    ->label('Tampil di')
                                    ->options([
                                        'all' => 'Semua (order + donasi)',
                                        'order' => 'Order lisensi saja',
                                        'donation' => 'Donasi saja',
                                    ])
                                    ->default('all')
                                    ->required(),
                                TextInput::make('account_number')
                                    ->label('Nomor Rekening / VA')
                                    ->placeholder('cth: 1234567890'),
                                TextInput::make('account_holder')
                                    ->label('Nama Pemegang Rekening')
                                    ->placeholder('cth: KomikTap'),
                                TextInput::make('qris_image_path')
                                    ->label('Path Gambar QRIS')
                                    ->placeholder('cth: qris/qris-bca.png')
                                    ->helperText('Path relatif di storage/app/public, tampil via /storage/...')
                                    ->columnSpanFull(),
                                Textarea::make('qris_string')
                                    ->label('QRIS String (dinamis)')
                                    ->placeholder('Tempel payload QRIS statis untuk dibuat dinamis per nominal')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Textarea::make('instructions')
                                    ->label('Instruksi Pembayaran (Markdown)')
                                    ->placeholder('Tulis langkah pembayaran…')
                                    ->rows(3)
                                    ->columnSpanFull(),
                            ])
                            ->columns(3)
                            ->collapsible()
                            ->collapsed(true)
                            ->addActionLabel('Tambah metode manual')
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ])
            ->statePath('data');
    }

    public function save(PaymentGatewaySettings $settings, PaymentSettings $paymentSettings)
    {
        $data = $this->form->getState();

        $settings->fansku_enabled = (bool) ($data['fansku_enabled'] ?? false);
        $settings->manual_enabled = (bool) ($data['manual_enabled'] ?? false);
        $settings->save();

        $methods = collect($data['payment_methods'] ?? [])
            ->map(function ($method) {
                return [
                    'name' => $method['name'] ?? '',
                    'account_number' => $method['account_number'] ?? null,
                    'account_holder' => $method['account_holder'] ?? null,
                    'usage_type' => $method['usage_type'] ?? 'all',
                    'instructions' => $method['instructions'] ?? '',
                    'qris_image_path' => $method['qris_image_path'] ?? null,
                    'qris_string' => $method['qris_string'] ?? null,
                    'is_active' => (bool) ($method['is_active'] ?? true),
                ];
            })
            ->values()
            ->all();

        $paymentSettings->is_enabled = (bool) ($data['is_enabled'] ?? true);
        $paymentSettings->payment_methods = $methods;
        $paymentSettings->save();

        $activeCount = collect($methods)->where('is_active', true)->count();

        Notification::make()
            ->title('Pengaturan pembayaran tersimpan.')
            ->body('QRIS Otomatis: ' . ($settings->fansku_enabled ? 'aktif' : 'nonaktif') . ' • Manual: ' . ($settings->manual_enabled ? "aktif ({$activeCount} metode)" : 'nonaktif'))
            ->success()
            ->send();
    }
}
