<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PartnersTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $partners = [
            [
                'id' => 1,
                'name' => '{"ar":"شريك 1","en":"partner 1"}',
                'email' => NULL,
                'phone' => NULL,
                'address' => NULL,
                'image' => 'partners_images/01J3BP256V6TNB7018YA08RNQM.webp',
                'show_on_homepage' => 1,
                'active' => 1,
                'deleted_at' => NULL,
                'created_at' => Carbon::parse('2024-07-22 01:19:42'),
                'updated_at' => Carbon::parse('2024-07-22 01:24:03'),
            ],
            [
                'id' => 2,
                'name' => '{"ar":"شريك 2","en":"partner 2"}',
                'email' => NULL,
                'phone' => NULL,
                'address' => NULL,
                'image' => 'partners_images/01J3BP4X8PBHX8TWGDG9NE7V40.webp',
                'show_on_homepage' => 1,
                'active' => 1,
                'deleted_at' => NULL,
                'created_at' => Carbon::parse('2024-07-22 01:21:12'),
                'updated_at' => Carbon::parse('2024-07-22 01:25:40'),
            ],
            [
                'id' => 3,
                'name' => '{"ar":"شريك 3 ","en":"partner 3"}',
                'email' => NULL,
                'phone' => NULL,
                'address' => NULL,
                'image' => 'partners_images/01J3BP5VHSCCDQZ6CCSSQS2Y2S.webp',
                'show_on_homepage' => 1,
                'active' => 1,
                'deleted_at' => NULL,
                'created_at' => Carbon::parse('2024-07-22 01:21:43'),
                'updated_at' => Carbon::parse('2024-07-22 01:25:50'),
            ],
            [
                'id' => 4,
                'name' => '{"ar":"ِشريك 4","en":"partner 4"}',
                'email' => NULL,
                'phone' => NULL,
                'address' => NULL,
                'image' => 'partners_images/01J3BP6KV26R946JVJK9G2F2SV.webp',
                'show_on_homepage' => 0,
                'active' => 1,
                'deleted_at' => NULL,
                'created_at' => Carbon::parse('2024-07-22 01:22:08'),
                'updated_at' => Carbon::parse('2024-07-22 01:27:02'),
            ],
            [
                'id' => 5,
                'name' => '{"ar":"شريك 5","en":"partner 5"}',
                'email' => NULL,
                'phone' => NULL,
                'address' => NULL,
                'image' => 'partners_images/01J3BP79TFGQFV6H31G396EB79.webp',
                'show_on_homepage' => 1,
                'active' => 1,
                'deleted_at' => NULL,
                'created_at' => Carbon::parse('2024-07-22 01:22:30'),
                'updated_at' => Carbon::parse('2024-07-22 01:26:09'),
            ],
            [
                'id' => 6,
                'name' => '{"ar":"شريك 6","en":"partner 6"}',
                'email' => NULL,
                'phone' => NULL,
                'address' => NULL,
                'image' => 'partners_images/01J3BP7WBHTR2BW0PCX5FZX9FJ.webp',
                'show_on_homepage' => 1,
                'active' => 1,
                'deleted_at' => NULL,
                'created_at' => Carbon::parse('2024-07-22 01:22:49'),
                'updated_at' => Carbon::parse('2024-07-22 01:26:18'),
            ],
            [
                'id' => 7,
                'name' => '{"ar":"شريك 7","en":"partner 7"}',
                'email' => NULL,
                'phone' => NULL,
                'address' => NULL,
                'image' => 'partners_images/01J3BP9A8FYXFNP8GPWQYXBG6E.webp',
                'show_on_homepage' => 0,
                'active' => 1,
                'deleted_at' => NULL,
                'created_at' => Carbon::parse('2024-07-22 01:23:36'),
                'updated_at' => Carbon::parse('2024-07-22 01:26:59'),
            ],
        ];

        DB::table('partners')->upsert($partners, ['id'], ['name', 'email', 'phone', 'address', 'image', 'show_on_homepage', 'active', 'deleted_at', 'updated_at']);
    }
}
