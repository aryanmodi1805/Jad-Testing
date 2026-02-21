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
        // Add iOS In-App Purchase fields to pricing_plans table
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->string('apple_product_id')->nullable()->after('month_price')->comment('Apple In-App Purchase Product ID');
            $table->boolean('is_ios_active')->default(false)->after('apple_product_id')->comment('Whether this plan is active for iOS');
            $table->decimal('ios_price', 8, 2)->nullable()->after('is_ios_active')->comment('Price for iOS before VAT');
            $table->decimal('ios_price_with_vat', 8, 2)->nullable()->after('ios_price')->comment('Price for iOS including VAT');
        });

        // Add iOS In-App Purchase fields to packages table
        Schema::table('packages', function (Blueprint $table) {
            $table->string('apple_product_id')->nullable()->after('price')->comment('Apple In-App Purchase Product ID');
            $table->boolean('is_ios_active')->default(false)->after('apple_product_id')->comment('Whether this package is active for iOS');
            $table->decimal('ios_price', 8, 2)->nullable()->after('is_ios_active')->comment('Price for iOS before VAT');
            $table->decimal('ios_price_with_vat', 8, 2)->nullable()->after('ios_price')->comment('Price for iOS including VAT');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->dropColumn(['apple_product_id', 'is_ios_active', 'ios_price', 'ios_price_with_vat']);
        });

        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn(['apple_product_id', 'is_ios_active', 'ios_price', 'ios_price_with_vat']);
        });
    }
};
