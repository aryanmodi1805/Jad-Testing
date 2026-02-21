<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SellerSocialMediaTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $socialMedia = [
            [
                'id' => 1,
                'seller_id' => 2,
                'platform' => 'تويترv',
                'link' => 'https://youtube.com',
                'created_at' => '2024-05-20 19:40:59',
                'updated_at' => '2024-07-25 01:59:51',
                'icon' => '01J3KFHTY3W9XX0TKK51S1CCMN.png',
                'active' => 1,
            ],
            [
                'id' => 2,
                'seller_id' => 2,
                'platform' => 'فيسبوك',
                'link' => 'https://facebook.com',
                'created_at' => '2024-05-21 14:22:19',
                'updated_at' => '2024-05-21 14:37:33',
                'icon' => NULL,
                'active' => 1,
            ],
        ];

        foreach ($socialMedia as $media) {
            \DB::table('seller_social_media')->updateOrInsert(
                ['id' => $media['id']],
                $media
            );
        }
    }
}
