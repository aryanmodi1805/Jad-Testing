<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('default.fast_response_badge', 60);
    }

    public function down()
    {
        $this->migrator->delete('default.fast_response_badge');
    }
};
