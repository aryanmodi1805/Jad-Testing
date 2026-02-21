<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SellersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $sellers = [
            [
                'id' => 2,
                'name' => 'seller1',
                'company_name' => 'شركة ابداع',
                'website' => 'https://evantto.test/seller/settings/company-profile',
                'location' => NULL,
                'years_in_business' => 2,
                'company_description' => 'شركة شركة',
                'email' => 'seller1@gmail.com',
                'country_id' => 127,
                'phone' => '776655',
                'email_verified_at' => NULL,
                'blocked' => 0,
                'password' => bcrypt('seller123'),
                'remember_token' => 'rH6umRNNoJj87o2M0b2AX9mMuwKtsS5GSpgrTRNcp5B511mHlxB4xtwjDuxP',
                'deleted_at' => NULL,
                'created_at' => '2024-03-30 23:13:35',
                'updated_at' => '2024-07-24 16:51:24',
                'avatar_url' => '01J303DXF0CGPPFJ8B5W5JG1GW.png',
                'phone_verified_at' => NULL,
            ],
        ];

        foreach ($sellers as $seller) {
            \DB::table('sellers')->updateOrInsert(
                ['id' => $seller['id']],
                $seller
            );
        }
    }
}
