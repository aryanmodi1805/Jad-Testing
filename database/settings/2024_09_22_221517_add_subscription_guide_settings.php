<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('subscription_guide.premium_guide_ar', null);
        $this->migrator->add('subscription_guide.premium_guide_en', null);
        $this->migrator->add('subscription_guide.credit_guide_ar', null);
        $this->migrator->add('subscription_guide.credit_guide_en', null);


    }

    public function down()
    {
        $this->migrator->delete('subscription_guide.premium_guide_ar');
        $this->migrator->delete('subscription_guide.premium_guide_en');

        $this->migrator->delete('subscription_guide.credit_guide_ar');
        $this->migrator->delete('subscription_guide.credit_guide_en');

    }
};
