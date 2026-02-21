<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('about.about_title_en',null);
        $this->migrator->add('about.about_title_ar',null);
        $this->migrator->add('about.about_sub_title_en',null);
        $this->migrator->add('about.about_sub_title_ar',null);
    }

    public function down()
    {
        $this->migrator->delete('about.about_title_en');
        $this->migrator->delete('about.about_title_ar');
        $this->migrator->delete('about.about_sub_title_en');
        $this->migrator->delete('about.about_sub_title_ar');
    }
};
