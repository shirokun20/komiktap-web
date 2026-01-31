<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('donation.title', 'Dukung KomikTap');
        $this->migrator->add('donation.description', 'Bantu kami terus berkarya dan menyajikan komik terbaik untuk Anda.');
        $this->migrator->add('donation.target_amount', 10000000);
        $this->migrator->add('donation.is_active', true);
    }
};
