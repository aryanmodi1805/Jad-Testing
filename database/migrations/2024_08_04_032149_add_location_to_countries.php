<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('countries', function (Blueprint $table) {
           $table->json("location")->nullable()->after("code");
        });
       /* $mapsKey =env('GOOGLE_MAPS_API_KEY');
        foreach (\App\Models\Country::all() as $country){

            try {
                $details = json_decode(file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?key={$mapsKey}&address=".urlencode($country->getTranslations("name")["en"])),true);
                $country->location=[$details["results"][0]['geometry']["location"]["lat"],$details["results"][0]['geometry']["location"]["lng"]];
                $country->save();
            }catch (Exception $e){}

        }*/
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn("location");
        });
    }
};
