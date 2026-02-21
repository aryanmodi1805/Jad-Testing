<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('default.show_subscriptions_page', false );
    }

    public function down(): void
    {
        $this->migrator->delete('default.show_subscriptions_page');
    }
};


