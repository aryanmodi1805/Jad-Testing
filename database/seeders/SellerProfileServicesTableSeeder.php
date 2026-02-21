<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SellerProfileServicesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $services = [
            [
                'id' => 1,
                'seller_id' => 2,
                'service_title' => 'تنظيف المنازل',
                'service_description' => 'نقدم خدمة تنظيف المنازل',
                'created_at' => '2024-05-21 14:36:50',
                'updated_at' => '2024-05-21 14:43:02',
            ],
            [
                'id' => 2,
                'seller_id' => 2,
                'service_title' => 'اصلاح السيارات',
                'service_description' => 'نقدم خدمة اصلاح السيارات',
                'created_at' => '2024-05-21 15:12:26',
                'updated_at' => '2024-05-21 15:13:41',
            ],
        ];

        foreach ($services as $service) {
            \DB::table('seller_profile_services')->updateOrInsert(
                ['id' => $service['id']],
                $service
            );
        }
    }
}
