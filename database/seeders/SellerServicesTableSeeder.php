<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SellerServicesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('seller_services')->truncate()
;

        \DB::table('seller_services')->insert(array (
            0 =>
            array (
                'id' => 4,
                'seller_id' => 2,
                'service_id' => '9ba3b173-0fc2-4154-9f48-c03d350458ec',
                'created_at' => '2024-04-02 01:45:30',
                'updated_at' => '2024-07-24 18:29:28',
            ),
            1 =>
            array (
                'id' => 20,
                'seller_id' => 2,
                'service_id' => '9bb800eb-88a0-4a1d-a1bd-08819e7e0d99',
                'created_at' => '2024-05-29 20:43:30',
                'updated_at' => '2024-05-29 20:43:30',
            ),
            2 =>
            array (
                'id' => 21,
                'seller_id' => 2,
                'service_id' => '9bb80109-d788-48c9-8c16-b008eedd6c02',
                'created_at' => '2024-06-01 15:12:02',
                'updated_at' => '2024-06-01 15:12:02',
            ),
            3 =>
            array (
                'id' => 22,
                'seller_id' => 11,
                'service_id' => '9ba7f2f6-b847-424e-b7dc-a9f92f9bfb4d',
                'created_at' => '2024-06-02 21:15:32',
                'updated_at' => '2024-06-02 21:15:32',
            ),
            4 =>
            array (
                'id' => 23,
                'seller_id' => 2,
                'service_id' => '9ba7f2f6-b847-424e-b7dc-a9f92f9bfb4d',
                'created_at' => '2024-07-02 18:12:42',
                'updated_at' => '2024-07-24 18:16:43',
            ),
            5 =>
            array (
                'id' => 24,
                'seller_id' => 2,
                'service_id' => '9c698167-4739-45d1-ab30-44c9d4124801',
                'created_at' => '2024-07-14 21:59:42',
                'updated_at' => '2024-07-14 21:59:42',
            ),
        ));


    }
}
