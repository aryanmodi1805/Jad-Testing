<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {


        $this->migrator->delete('default.instagram');
        $this->migrator->add('default.instagram', null);
        $this->migrator->delete('default.facebook');
        $this->migrator->add('default.facebook', null);
        $this->migrator->delete('default.twitter');
        $this->migrator->add('default.twitter', null);
        $this->migrator->delete('default.linkedin');
        $this->migrator->add('default.linkedin', null);
        $this->migrator->delete('default.github');

        if(invade($this->migrator)->checkIfPropertyExists('default.default_city')){
            $this->migrator->rename('default.default_city', 'default.default_country');
        }
    }

    public function down()
    {
        $this->migrator->add('default.instagram', "#");
        $this->migrator->add('default.facebook', "#");
        $this->migrator->add('default.twitter', "#");
        $this->migrator->add('default.github', "#");
        $this->migrator->add('default.linkedin', "#");
        $this->migrator->rename('default.default_country', 'default.default_city');


    }
};
