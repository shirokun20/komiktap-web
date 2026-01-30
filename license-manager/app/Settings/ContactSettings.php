<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class ContactSettings extends Settings
{
    public string $whatsapp_number;
    public string $whatsapp_description;
    public string $email_address;
    public string $email_description;
    public string $discord_url;
    public string $discord_name;
    public string $discord_description;

    public static function group(): string
    {
        return 'contact';
    }
}
