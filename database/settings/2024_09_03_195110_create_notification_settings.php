<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('customer_notification.email_new_message', false);
        $this->migrator->add('customer_notification.email_new_estimate', false);
        $this->migrator->add('customer_notification.email_new_response', false);
        $this->migrator->add('customer_notification.email_accepted_invitation', false);
        $this->migrator->add('customer_notification.email_request_status_change', false);
        $this->migrator->add('customer_notification.push_new_message', false);
        $this->migrator->add('customer_notification.push_new_estimate', false);
        $this->migrator->add('customer_notification.push_new_response', false);
        $this->migrator->add('customer_notification.push_accepted_invitation', false);
        $this->migrator->add('customer_notification.push_request_status_change', false);

        $this->migrator->add('seller_notification.email_new_message', false);
        $this->migrator->add('seller_notification.email_invited', false);
        $this->migrator->add('seller_notification.email_new_request', false);
        $this->migrator->add('seller_notification.email_rated', false);
        $this->migrator->add('seller_notification.email_response_status_change', false);
        $this->migrator->add('seller_notification.push_new_message', false);
        $this->migrator->add('seller_notification.push_invited', false);
        $this->migrator->add('seller_notification.push_new_request', false);
        $this->migrator->add('seller_notification.push_rated', false);
        $this->migrator->add('seller_notification.push_response_status_change', false);


    }

    public function down(): void
    {
        $this->migrator->delete('customer_notification.email_new_message');
        $this->migrator->delete('customer_notification.email_new_estimate');
        $this->migrator->delete('customer_notification.email_new_response');
        $this->migrator->delete('customer_notification.email_accepted_invitation');
        $this->migrator->delete('customer_notification.email_request_status_change');
        $this->migrator->delete('customer_notification.push_new_message');
        $this->migrator->delete('customer_notification.push_new_estimate');
        $this->migrator->delete('customer_notification.push_new_response');
        $this->migrator->delete('customer_notification.push_accepted_invitation');
        $this->migrator->delete('customer_notification.push_request_status_change');

        $this->migrator->delete('seller_notification.email_new_message');
        $this->migrator->delete('seller_notification.email_invited');
        $this->migrator->delete('seller_notification.email_new_request');
        $this->migrator->delete('seller_notification.email_rated');
        $this->migrator->delete('seller_notification.email_response_status_change');
        $this->migrator->delete('seller_notification.push_new_message');
        $this->migrator->delete('seller_notification.push_invited');
        $this->migrator->delete('seller_notification.push_new_request');
        $this->migrator->delete('seller_notification.push_rated');
        $this->migrator->delete('seller_notification.push_response_status_change');


    }
};
