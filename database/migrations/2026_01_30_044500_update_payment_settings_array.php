<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // 1. Get old values if possible (optional, but good for persistence)
        // Since we are using Spatie Settings, we can't easily access old props if we changed the class
        // But we can just overwrite for now as it is a dev env.
        
        $this->migrator->add('payment.payment_methods', []);
        
        // Remove old keys
        $this->migrator->delete('payment.payment_method_name');
        $this->migrator->delete('payment.account_number');
        $this->migrator->delete('payment.account_holder');
        $this->migrator->delete('payment.instructions');
        $this->migrator->delete('payment.qris_image_path');
    }
};
