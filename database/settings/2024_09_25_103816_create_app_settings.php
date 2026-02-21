<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('app.ios_app_active', true);
        $this->migrator->add('app.android_app_active', true);
        $this->migrator->add('app.ios_min_app_version', 1);
        $this->migrator->add('app.android_min_app_version', 1);
    }

    public function down()
    {
        $this->migrator->delete('app.ios_app_active');
        $this->migrator->delete('app.android_app_active');
        $this->migrator->delete('app.ios_min_app_version');
        $this->migrator->delete('app.android_min_app_version');
    }
};
