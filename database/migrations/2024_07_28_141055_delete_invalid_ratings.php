<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DeleteInvalidRatings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $validRateableTypes = [
            'App\Models\Request',
            'App\Models\Response',
            'App\Models\Country',
        ];

        $validRaterTypes = [
            'App\Models\Seller',
            'App\Models\Customer',
        ];

        DB::table('ratings')
            ->whereNotIn('rateable_type', $validRateableTypes)
            ->orWhereNotIn('rater_type', $validRaterTypes)
            ->delete();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }
}
