<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('contact.whatsapp_number', '+62 812-3456-7890');
        $this->migrator->add('contact.whatsapp_description', 'Fast Response (09:00 - 21:00)');
        $this->migrator->add('contact.email_address', 'support@komiktap.id');
        $this->migrator->add('contact.email_description', 'Untuk kerjasama & bisnis');
        $this->migrator->add('contact.discord_url', '#');
        $this->migrator->add('contact.discord_name', 'KomikTap Official');
        $this->migrator->add('contact.discord_description', 'Gabung komunitas pembaca');
    }
};
