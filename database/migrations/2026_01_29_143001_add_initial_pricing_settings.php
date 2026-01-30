<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $this->migrator->add('pricing.ketengan_base_price', 15000.0);
        $this->migrator->add('pricing.discount_3_months', 0.05);
        $this->migrator->add('pricing.discount_6_months', 0.10);
        $this->migrator->add('pricing.discount_12_months', 0.20);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
