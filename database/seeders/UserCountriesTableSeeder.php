<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserCountriesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $userCountries = [
            [
                'user_id' => 1,
                'country_id' => 1,
            ],
            [
                'user_id' => 1,
                'country_id' => 127,
            ],
        ];

        foreach ($userCountries as $userCountry) {
            DB::table('user_countries')->updateOrInsert(
                ['user_id' => $userCountry['user_id'], 'country_id' => $userCountry['country_id']],
                $userCountry
            );
        }
    }
}
