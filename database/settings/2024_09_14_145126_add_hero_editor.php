<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('heroes.text_ar', null);
        $this->migrator->add('heroes.text_en', null);
    }

    public function down()
    {
        $this->migrator->delete('heroes.text_ar');
        $this->migrator->delete('heroes.text_en');
    }
};
