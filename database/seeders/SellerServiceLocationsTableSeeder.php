<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SellerServiceLocationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('seller_service_locations')->truncate()
;

        \DB::table('seller_service_locations')->insert(array (
            0 =>
            array (
                'id' => 429,
                'seller_service_id' => 22,
                'seller_location_id' => 40,
                'created_at' => '2024-07-02 19:50:09',
                'updated_at' => '2024-07-02 19:50:09',
            ),
            1 =>
            array (
                'id' => 444,
                'seller_service_id' => 23,
                'seller_location_id' => 41,
                'created_at' => '2024-07-02 20:59:39',
                'updated_at' => '2024-07-02 20:59:39',
            ),
            2 =>
            array (
                'id' => 473,
                'seller_service_id' => 24,
                'seller_location_id' => 36,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            3 =>
            array (
                'id' => 474,
                'seller_service_id' => 24,
                'seller_location_id' => 37,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            4 =>
            array (
                'id' => 475,
                'seller_service_id' => 24,
                'seller_location_id' => 38,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            5 =>
            array (
                'id' => 476,
                'seller_service_id' => 24,
                'seller_location_id' => 39,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            6 =>
            array (
                'id' => 477,
                'seller_service_id' => 24,
                'seller_location_id' => 41,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            7 =>
            array (
                'id' => 478,
                'seller_service_id' => 24,
                'seller_location_id' => 42,
                'created_at' => NULL,
                'updated_at' => NULL,
            ),
            8 =>
            array (
                'id' => 484,
                'seller_service_id' => 20,
                'seller_location_id' => 43,
                'created_at' => '2024-07-15 20:02:33',
                'updated_at' => '2024-07-15 20:02:33',
            ),
            9 =>
            array (
                'id' => 485,
                'seller_service_id' => 21,
                'seller_location_id' => 43,
                'created_at' => '2024-07-15 20:02:33',
                'updated_at' => '2024-07-15 20:02:33',
            ),
            10 =>
            array (
                'id' => 486,
                'seller_service_id' => 23,
                'seller_location_id' => 43,
                'created_at' => '2024-07-15 20:02:33',
                'updated_at' => '2024-07-15 20:02:33',
            ),
            11 =>
            array (
                'id' => 487,
                'seller_service_id' => 24,
                'seller_location_id' => 43,
                'created_at' => '2024-07-15 20:02:33',
                'updated_at' => '2024-07-15 20:02:33',
            ),
            12 =>
            array (
                'id' => 488,
                'seller_service_id' => 4,
                'seller_location_id' => 43,
                'created_at' => '2024-07-15 20:02:33',
                'updated_at' => '2024-07-15 20:02:33',
            ),
        ));


    }
}
