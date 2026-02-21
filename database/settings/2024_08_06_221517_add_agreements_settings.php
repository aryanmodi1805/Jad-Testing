<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('default.customer_agreement_en', null);
        $this->migrator->add('default.customer_agreement_ar', null);
        $this->migrator->add('default.seller_agreement_en', null);
        $this->migrator->add('default.seller_agreement_ar', null);

    }

    public function down()
    {
        $this->migrator->delete('default.customer_agreement_en');
        $this->migrator->delete('default.customer_agreement_ar');
        $this->migrator->delete('default.seller_agreement_en');
        $this->migrator->delete('default.seller_agreement_ar');
    }
};
