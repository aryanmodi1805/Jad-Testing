<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('default.customers_count', 0);
        $this->migrator->add('default.projects_completed', 0);
        $this->migrator->add('default.teams_count', 0);
    }

    public function down()
    {
        $this->migrator->delete('default.customers_count');
        $this->migrator->delete('default.projects_completed');
        $this->migrator->delete('default.teams_count');
    }
};
