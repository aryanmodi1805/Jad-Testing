<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {

        $this->migrator->add('pricing.step_1_title_en',null);
        $this->migrator->add('pricing.step_1_title_ar',null);
        $this->migrator->add('pricing.step_1_description_en',null);
        $this->migrator->add('pricing.step_1_description_ar',null);
        $this->migrator->add('pricing.step_1_image',null);
        $this->migrator->add('pricing.step_2_title_en',null);
        $this->migrator->add('pricing.step_2_title_ar',null);
        $this->migrator->add('pricing.step_2_description_en',null);
        $this->migrator->add('pricing.step_2_description_ar',null);
        $this->migrator->add('pricing.step_2_image',null);
        $this->migrator->add('pricing.step_3_title_en',null);
        $this->migrator->add('pricing.step_3_title_ar',null);
        $this->migrator->add('pricing.step_3_description_en',null);
        $this->migrator->add('pricing.step_3_description_ar',null);
        $this->migrator->add('pricing.step_3_image',null);
        $this->migrator->add('pricing.step_4_title_en',null);
        $this->migrator->add('pricing.step_4_title_ar',null);
        $this->migrator->add('pricing.step_4_description_en',null);
        $this->migrator->add('pricing.step_4_description_ar',null);
        $this->migrator->add('pricing.step_4_image',null);

    }

    public function down()
    {
        $this->migrator->delete('pricing.step_1_title_en');
        $this->migrator->delete('pricing.step_1_title_ar');
        $this->migrator->delete('pricing.step_1_description_en');
        $this->migrator->delete('pricing.step_1_description_ar');
        $this->migrator->delete('pricing.step_1_image');
        $this->migrator->delete('pricing.step_2_title_en');
        $this->migrator->delete('pricing.step_2_title_ar');
        $this->migrator->delete('pricing.step_2_description_en');
        $this->migrator->delete('pricing.step_2_description_ar');
        $this->migrator->delete('pricing.step_2_image');
        $this->migrator->delete('pricing.step_3_title_en');
        $this->migrator->delete('pricing.step_3_title_ar');
        $this->migrator->delete('pricing.step_3_description_en');
        $this->migrator->delete('pricing.step_3_description_ar');
        $this->migrator->delete('pricing.step_3_image');
        $this->migrator->delete('pricing.step_4_title_en');
        $this->migrator->delete('pricing.step_4_title_ar');
        $this->migrator->delete('pricing.step_4_description_en');
        $this->migrator->delete('pricing.step_4_description_ar');
        $this->migrator->delete('pricing.step_4_image');
    }
};
