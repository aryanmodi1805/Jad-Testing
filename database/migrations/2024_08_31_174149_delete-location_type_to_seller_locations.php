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
            $table->dropColumn('location_type');
            $table->longText('location_name')->nullable()->after('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_locations', function (Blueprint $table) {
            $table->string('location_type')->default('nationwide');
            $table->dropColumn('location_name');
        });
    }
};
