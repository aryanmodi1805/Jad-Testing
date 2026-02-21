<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        DB::unprepared('SET GLOBAL log_bin_trust_function_creators = 1;');
        DB::unprepared('
            CREATE FUNCTION request_distance(latRequest DOUBLE, lonRequest DOUBLE, lat DOUBLE, lon DOUBLE) RETURNS DOUBLE
            BEGIN
                RETURN 6371 * acos(
                    cos(radians(latRequest)) * cos(radians(lat)) * cos(radians(lon) - radians(lonRequest)) +
                    sin(radians(latRequest)) * sin(radians(lat))
                );
            END
        ');
    }

    public function down()
    {
        DB::unprepared('DROP FUNCTION IF EXISTS request_distance');
    }
};
