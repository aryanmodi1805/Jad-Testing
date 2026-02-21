<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('social_media.youtube', null);
        $this->migrator->add('social_media.tiktok', null);
    }

    public function down(): void
    {
        $this->migrator->delete('social_media.youtube');
        $this->migrator->delete('social_media.tiktok');
    }
};
