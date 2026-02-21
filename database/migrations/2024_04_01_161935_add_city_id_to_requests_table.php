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
        Schema::table('requests', function (Blueprint $table) {
            $table->string('location_name')->nullable()->after('status');
            $table->double('latitude')->nullable()->after('location_name');
            $table->double('longitude')->nullable()->after('latitude');
            $table->string('location_type')->default('specific')->after('longitude');
            $table->foreignId('city_id')->nullable()->after('location_type')->constrained()->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            //
        });
    }
};
