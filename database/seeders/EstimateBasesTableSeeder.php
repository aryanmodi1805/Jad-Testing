<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstimateBasesTableSeeder extends Seeder
{
    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        $estimateBases = [
            [
                'id' => 1,
                'name' => '{"ar":"في الجلسة","en":"/session"}',
                'active' => 1,
                'deleted_at' => NULL,
                'created_at' => Carbon::parse('2024-05-18 12:57:58'),
                'updated_at' => Carbon::parse('2024-05-29 16:34:59'),
            ],
            [
                'id' => 2,
                'name' => '{"ar":"في اليوم","en":"/day"}',
                'active' => 1,
                'deleted_at' => NULL,
                'created_at' => Carbon::parse('2024-05-18 12:58:09'),
                'updated_at' => Carbon::parse('2024-05-18 12:58:14'),
            ],
        ];

        DB::table('estimate_bases')->upsert($estimateBases, ['id'], ['name', 'active', 'deleted_at', 'updated_at']);
    }
}
