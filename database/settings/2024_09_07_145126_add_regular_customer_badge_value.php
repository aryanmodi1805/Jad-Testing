<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('default.regular_customer_badge', 5);
    }

    public function down()
    {
        $this->migrator->delete('default.regular_customer_badge');
    }
};
