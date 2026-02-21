<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {

        \DB::table('users')->updateOrInsert(
            [
                'id' => 1,
            ],
            [
                'id' => 1,
                'name' => 'admin',
                'email' => 'admin@admin.com',
                'email_verified_at' => NULL,
                'password' => bcrypt('admin123'),
                'created_at' => '2024-03-25 13:53:01',
                'updated_at' => '2024-07-27 16:51:21',
                'theme' => 'default',
                'theme_color' => NULL,
                'stripe_id' => NULL,
                'pm_type' => NULL,
                'pm_last_four' => NULL,
                'trial_ends_at' => NULL,
                'last_country_id' => 127,
            ]
        );

    }
}
