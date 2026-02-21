<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('wallet.credit_price', 0.0);
        $this->migrator->add('wallet.supported_currencies', []);
    }
};
