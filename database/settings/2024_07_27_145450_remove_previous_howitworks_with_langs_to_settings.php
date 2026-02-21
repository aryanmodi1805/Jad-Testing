<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->deleteIfExists('howCustomer.step_1_title');
        $this->migrator->deleteIfExists('howCustomer.step_1_description');
        $this->migrator->deleteIfExists('howCustomer.step_1_image');
        $this->migrator->deleteIfExists('howCustomer.step_2_title');
        $this->migrator->deleteIfExists('howCustomer.step_2_description');
        $this->migrator->deleteIfExists('howCustomer.step_2_image');
        $this->migrator->deleteIfExists('howCustomer.step_3_title');
        $this->migrator->deleteIfExists('howCustomer.step_3_description');
        $this->migrator->deleteIfExists('howCustomer.step_3_image');
        $this->migrator->deleteIfExists('howCustomer.step_4_title');
        $this->migrator->deleteIfExists('howCustomer.step_4_description');
        $this->migrator->deleteIfExists('howCustomer.step_4_image');
        $this->migrator->deleteIfExists('howSeller.step_1_title');
        $this->migrator->deleteIfExists('howSeller.step_1_description');
        $this->migrator->deleteIfExists('howSeller.step_1_image');
        $this->migrator->deleteIfExists('howSeller.step_2_title');
        $this->migrator->deleteIfExists('howSeller.step_2_description');
        $this->migrator->deleteIfExists('howSeller.step_2_image');
        $this->migrator->deleteIfExists('howSeller.step_3_title');
        $this->migrator->deleteIfExists('howSeller.step_3_description');
        $this->migrator->deleteIfExists('howSeller.step_3_image');
        $this->migrator->deleteIfExists('howSeller.step_4_title');
        $this->migrator->deleteIfExists('howSeller.step_4_description');
        $this->migrator->deleteIfExists('howSeller.step_4_image');


    }


};
