<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // Update kontak produksi: WA + email baru.
        if ($this->migrator->exists('contact.whatsapp_number')) {
            $this->migrator->update('contact.whatsapp_number', fn () => '+62 895-7051-98453');
        }
        if ($this->migrator->exists('contact.email_address')) {
            $this->migrator->update('contact.email_address', fn () => 'leonidasfgo@gmail.com');
        }
    }
};
