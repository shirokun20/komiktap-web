<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contact.whatsapp_number', '+62 895-7051-98453');
        $this->migrator->add('contact.whatsapp_description', 'Fast Response (09:00 - 21:00)');
        $this->migrator->add('contact.email_address', 'leonidasfgo@gmail.com');
        $this->migrator->add('contact.email_description', 'Untuk kerjasama & bisnis');
        // Discord tidak dipakai — tidak ada komunitas Discord.
        // Nilai lama (discord_url/name/description) dibiarkan apa adanya bila sudah
        // ada di DB agar migrasi tetap aman di semua environment.
    }
};
