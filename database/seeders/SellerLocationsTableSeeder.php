<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SellerLocationsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {


        \DB::table('seller_locations')->truncate()
;

        \DB::table('seller_locations')->insert(array (
            0 =>
            array (
                'id' => 36,
                'seller_id' => 2,
                'name' => 'حي, TADD6396, 6396 طريق وادي وج، 2946، الشرقية، الطائف 26523، السعودية',
                'is_nationwide' => 0,
                'city_id' => NULL,
                'location_type' => 'SA',
                'country_id' => 127,
                'latitude' => 21.276572918729,
                'longitude' => 40.422258571094,
                'location_range' => 50,
                'created_at' => '2024-05-14 19:11:48',
                'updated_at' => '2024-05-14 19:24:46',
            ),
            1 =>
            array (
                'id' => 37,
                'seller_id' => 2,
                'name' => 'GGDA4260، 4260 الحارث بن ضرار الخزاعي، 6970، حي المطار، جازان 82722، السعودية',
                'is_nationwide' => 0,
                'city_id' => NULL,
                'location_type' => 'SA',
                'country_id' => 127,
                'latitude' => 16.887328848131,
                'longitude' => 42.578139365625,
                'location_range' => 50,
                'created_at' => '2024-05-14 21:46:40',
                'updated_at' => '2024-05-14 21:46:40',
            ),
            2 =>
            array (
                'id' => 38,
                'seller_id' => 2,
                'name' => 'ATSB7066، 7066 عرقة ال سليمان 2، 5497، الصبيخة 62879، السعودية',
                'is_nationwide' => 0,
                'city_id' => NULL,
                'location_type' => 'SA',
                'country_id' => 127,
                'latitude' => 18.940193636513,
                'longitude' => 43.412925875,
                'location_range' => 1,
                'created_at' => '2024-05-29 19:58:10',
                'updated_at' => '2024-05-29 19:58:10',
            ),
            3 =>
            array (
                'id' => 39,
                'seller_id' => 2,
                'name' => '8562 مسعود بن عبدالعزيز، المنشية القديمة، تبوك 47914، السعودية',
                'is_nationwide' => 0,
                'city_id' => NULL,
                'location_type' => 'YE',
                'country_id' => 127,
                'latitude' => 28.3835079,
                'longitude' => 36.5661908,
                'location_range' => 500,
                'created_at' => '2024-06-01 22:45:11',
                'updated_at' => '2024-06-01 22:45:11',
            ),
            5 =>
            array (
                'id' => 41,
                'seller_id' => 2,
                'name' => 'الرياض السعودية',
                'is_nationwide' => 0,
                'city_id' => NULL,
                'location_type' => 'nationwide',
                'country_id' => 127,
                'latitude' => 24.7135517,
                'longitude' => 46.6752957,
                'location_range' => 5,
                'created_at' => '2024-06-29 20:41:03',
                'updated_at' => '2024-06-29 21:02:57',
            ),
            6 =>
            array (
                'id' => 42,
                'seller_id' => 2,
                'name' => 'nationwide',
                'is_nationwide' => 1,
                'city_id' => NULL,
                'location_type' => 'nationwide',
                'country_id' => 127,
                'latitude' => NULL,
                'longitude' => NULL,
                'location_range' => NULL,
                'created_at' => '2024-06-29 21:03:06',
                'updated_at' => '2024-06-29 21:03:22',
            ),
            7 =>
            array (
                'id' => 43,
                'seller_id' => 2,
                'name' => 'جدة السعودية',
                'is_nationwide' => 0,
                'city_id' => NULL,
                'location_type' => 'SA',
                'country_id' => 127,
                'latitude' => 21.52,
                'longitude' => 39.1610863,
                'location_range' => 50,
                'created_at' => '2024-07-02 19:01:15',
                'updated_at' => '2024-07-02 21:22:13',
            ),
        ));


    }
}
