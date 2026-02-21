<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class SellerQASTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $qas = [
            [
                'id' => 1,
                'q_a_s_id' => 1,
                'seller_id' => 2,
                'answer' => '<p>dd</p>',
                'created_at' => '2024-05-20 19:23:43',
                'updated_at' => '2024-05-20 19:33:47',
            ],
            [
                'id' => 2,
                'q_a_s_id' => 2,
                'seller_id' => 2,
                'answer' => 'مالذي',
                'created_at' => '2024-05-20 19:23:43',
                'updated_at' => '2024-05-20 19:23:43',
            ],
            [
                'id' => 3,
                'q_a_s_id' => 3,
                'seller_id' => 2,
                'answer' => '<p dir="rtl">لماذا</p>',
                'created_at' => '2024-05-20 19:23:43',
                'updated_at' => '2024-05-21 14:07:04',
            ],
            [
                'id' => 4,
                'q_a_s_id' => 4,
                'seller_id' => 2,
                'answer' => 'ماهي',
                'created_at' => '2024-05-20 19:23:43',
                'updated_at' => '2024-05-20 19:23:43',
            ],
        ];

        foreach ($qas as $qa) {
            \DB::table('seller_q_a_s')->updateOrInsert(
                ['id' => $qa['id']],
                $qa
            );
        }
    }
}
