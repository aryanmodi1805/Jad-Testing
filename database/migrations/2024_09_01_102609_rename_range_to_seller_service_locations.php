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
            $table->renameColumn('range', 'location_range');
        });

        Schema::table('seller_locations', function (Blueprint $table) {
            $table->renameColumn('range', 'location_range');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_service_locations', function (Blueprint $table) {
            $table->renameColumn('location_range', 'range');
        });

        Schema::table('seller_locations', function (Blueprint $table) {
            $table->renameColumn('location_range', 'range');
        });
    }
};
