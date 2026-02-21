<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('auth.login_page_image', null);
        $this->migrator->add('auth.register_page_image', null);
    }

    public function down()
    {
        $this->migrator->delete('auth.login_page_image');
        $this->migrator->delete('auth.register_page_image');
    }
};
