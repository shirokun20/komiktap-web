<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('payment_gateway.fansku_enabled', (bool) env('FANSKU_IS_ENABLED', false));
        $this->migrator->add('payment_gateway.manual_enabled', false);
    }
};
