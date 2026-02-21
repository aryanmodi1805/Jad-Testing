<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class CustomersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $customers = [
            [
                'id' => 1,
                'name' => 'محمد',
                'email' => 'mohammed@gmail.com',
                'avatar_url' => '01J3JFW610ADF1WGTB7BWN2BR2.png',
                'country_id' => 127,
                'phone' => '744444432',
                'email_verified_at' => NULL,
                'blocked' => 0,
                'password' => bcrypt('customer123'),
                'deleted_at' => NULL,
                'created_at' => '2024-03-25 13:56:13',
                'updated_at' => '2024-07-27 17:34:12',
                'phone_verified_at' => '2024-07-27 17:34:12',
            ],
            [
                'id' => 2,
                'name' => 'يوسف',
                'email' => 'yousef@yousef.com',
                'avatar_url' => NULL,
                'country_id' => 127,
                'phone' => '77777777777',
                'email_verified_at' => NULL,
                'blocked' => 0,
                'password' => bcrypt('customer123'),
                'deleted_at' => NULL,
                'created_at' => '2024-04-07 16:31:42',
                'updated_at' => '2024-04-07 16:31:42',
                'phone_verified_at' => NULL,
            ],
            [
                'id' => 4,
                'name' => 'customer',
                'email' => 'customer3@customer.com',
                'avatar_url' => NULL,
                'country_id' => 127,
                'phone' => NULL,
                'email_verified_at' => NULL,
                'blocked' => 0,
                'password' => bcrypt('customer123'),
                'deleted_at' => NULL,
                'created_at' => '2024-06-11 00:08:10',
                'updated_at' => '2024-06-11 00:08:10',
                'phone_verified_at' => NULL,
            ],
        ];

        foreach ($customers as $customer) {
            \DB::table('customers')->updateOrInsert(
                ['id' => $customer['id']],
                $customer
            );
        }
    }
}
