<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('default.about_en',null);
        $this->migrator->add('default.about_ar',null);
        $this->migrator->add('default.about_image',null);
    }

    public function down()
    {
        $this->migrator->delete('default.about_en');
        $this->migrator->delete('default.about_ar');
        $this->migrator->delete('default.about_image');
    }
};
