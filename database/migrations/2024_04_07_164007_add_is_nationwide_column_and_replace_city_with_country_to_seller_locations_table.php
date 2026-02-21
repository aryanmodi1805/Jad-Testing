<?php

use App\Models\Country;
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
            $table->boolean('is_nationwide')->nullable()->after('name');

            $table->foreignIdFor(Country::class)->nullable()->after('location_type')->constrained()->nullOnDelete();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_locations', function (Blueprint $table) {
            $table->dropColumn('is_nationwide');
        });
    }
};
