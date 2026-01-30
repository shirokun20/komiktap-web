<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('payment.is_enabled', true);
        $this->migrator->add('payment.payment_method_name', 'Bank Transfer / QRIS');
        $this->migrator->add('payment.account_number', '1234567890');
        $this->migrator->add('payment.account_holder', 'PT KomikTap Indonesia');
        $this->migrator->add('payment.instructions', "Silakan transfer ke rekening di atas dan kirim bukti pembayaran via WhatsApp.\n\n**Note**: Proses verifikasi maksimal 1x24 jam.");
        $this->migrator->add('payment.qris_image_path', null);
    }
};
