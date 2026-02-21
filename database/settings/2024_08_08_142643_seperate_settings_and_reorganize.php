<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->rename('default.about_en','about.about_en');
        $this->migrator->rename('default.about_ar','about.about_ar');
        $this->migrator->rename('default.about_image','about.about_image');

        $this->migrator->rename('default.location_en', 'about.location_en');
        $this->migrator->rename('default.location_ar', 'about.location_ar');
        $this->migrator->rename('default.location_map', 'about.location_map');
        $this->migrator->rename('default.phone', 'about.phone');
        $this->migrator->rename('default.email', 'about.email');

        $this->migrator->rename('default.instagram', 'social_media.instagram');
        $this->migrator->rename('default.facebook', 'social_media.facebook');
        $this->migrator->rename('default.x', 'social_media.x');
        $this->migrator->rename('default.linkedin', 'social_media.linkedin');



        $this->migrator->rename('default.privacy_policy_en', 'privacy_policy.privacy_policy_en');
        $this->migrator->rename('default.privacy_policy_ar', 'privacy_policy.privacy_policy_ar');

        $this->migrator->rename('default.customer_agreement_en', 'customer_agreement.customer_agreement_en');
        $this->migrator->rename('default.customer_agreement_ar', 'customer_agreement.customer_agreement_ar');

        $this->migrator->rename('default.seller_agreement_en', 'seller_agreement.seller_agreement_en');
        $this->migrator->rename('default.seller_agreement_ar', 'seller_agreement.seller_agreement_ar');

        $this->migrator->rename('request.request_status', 'default.request_status');
        $this->migrator->rename('request.maximum_responses', 'default.maximum_responses');


    }

    public function down()
    {
        $this->migrator->rename('about.about_en','default.about_en');
        $this->migrator->rename('about.about_ar','default.about_ar');
        $this->migrator->rename('about.about_image','default.about_image');

        $this->migrator->rename('about.location_en', 'default.location_en');
        $this->migrator->rename('about.location_ar', 'default.location_ar');
        $this->migrator->rename('about.location_map', 'default.location_map');
        $this->migrator->rename('about.phone', 'default.phone');
        $this->migrator->rename('about.email', 'default.email');

        $this->migrator->rename('privacy_policy.privacy_policy_en', 'default.privacy_policy_en');
        $this->migrator->rename('privacy_policy.privacy_policy_ar', 'default.privacy_policy_ar');

        $this->migrator->rename('customer_agreement.customer_agreement_en', 'default.customer_agreement_en');
        $this->migrator->rename('customer_agreement.customer_agreement_ar', 'default.customer_agreement_ar');

        $this->migrator->rename('seller_agreement.seller_agreement_en', 'default.seller_agreement_en');
        $this->migrator->rename('seller_agreement.seller_agreement_ar', 'default.seller_agreement_ar');

        $this->migrator->rename('social_media.instagram', 'default.instagram');
        $this->migrator->rename('social_media.facebook', 'default.facebook');
        $this->migrator->rename('social_media.x', 'default.x');
        $this->migrator->rename('social_media.linkedin', 'default.linkedin');

        $this->migrator->rename('default.request_status', 'request.request_status');
        $this->migrator->rename('default.maximum_responses', 'request.maximum_responses');




    }
};
