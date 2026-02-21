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
        Schema::table('seller_service_locations', function (Blueprint $table) {
            $table->integer('range')->nullable()->after('seller_location_id');
            $table->boolean('is_nationwide')->default(false)->after('seller_location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_service_locations', function (Blueprint $table) {
            $table->dropColumn('range');
            $table->dropColumn('is_nationwide');
        });
    }
};
