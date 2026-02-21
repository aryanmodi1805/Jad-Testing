<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration {
    public function up(): void
    {
        $this->migrator->add('default.privacy_policy_en', null);
        $this->migrator->add('default.privacy_policy_ar', null);

    }

    public function down()
    {
        $this->migrator->delete('default.privacy_policy_en');
        $this->migrator->delete('default.privacy_policy_ar');
    }
};
