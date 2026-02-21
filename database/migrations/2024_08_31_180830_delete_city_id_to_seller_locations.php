
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
        Schema::table('seller_locations', function (Blueprint $table) {
            if (Schema::hasColumn('seller_locations', 'city_id')) {
                $table->dropColumn('city_id');
            }
        });
    }

};
