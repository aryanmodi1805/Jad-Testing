<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('default.default_city', "SA");
        $this->migrator->add('default.instagram', "#");
        $this->migrator->add('default.facebook', "#");
        $this->migrator->add('default.twitter', "#");
        $this->migrator->add('default.github', "#");
        $this->migrator->add('default.linkedin', "#");
    }
};
