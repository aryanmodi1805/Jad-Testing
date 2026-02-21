<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {

        $this->migrator->add('default.location_en', null);
        $this->migrator->add('default.location_ar', null);
        $this->migrator->add('default.location_map', null);
        $this->migrator->add('default.phone', null);
        $this->migrator->add('default.email', null);
    }

    public function down()
    {
        $this->migrator->delete('default.location_en');
        $this->migrator->delete('default.location_ar');
        $this->migrator->delete('default.location_map');
        $this->migrator->delete('default.phone');
        $this->migrator->delete('default.email');
    }
};
