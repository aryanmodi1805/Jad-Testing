<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('heroes.main_hero',null );
        $this->migrator->add('heroes.sub_hero',null );
    }

    public function down()
    {
        $this->migrator->delete('heroes.main_hero');
        $this->migrator->delete('heroes.sub_hero');
    }
};
