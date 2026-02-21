<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('pricing_plans')->truncate();
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->bigIncrements('id')->change();
            /*==================================================================*/
            $table->boolean('is_subscribed_all')->default(false)->after('year_price');
            $table->boolean('is_in_main_category')->default(false)->after('is_subscribed_all');
            $table->boolean('is_in_sub_category')->default(false)->after('is_in_main_category');
            $table->boolean('is_in_service')->default(false)->after('is_in_sub_category');
            $table->boolean('is_premium')->default(false)->after('is_in_service');
            $table->boolean('is_unlimited')->default(false)->after('is_premium');
            $table->integer('main_category_limit')->default(0)->after('is_unlimited');
            $table->integer('sub_category_limit')->default(0)->after('main_category_limit');
            $table->integer('service_limit')->default(0)->after('sub_category_limit');
            $table->integer('credit_limit')->default(0)->after('service_limit');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->dropColumn([
                'is_subscribed_all',
                'is_in_main_category',
                'is_in_sub_category',
                'is_premium',
                'is_unlimited',
                'main_category_limit',
                'sub_category_limit',
                'service_limit',
                'credit_limit',
                'is_in_service',

            ]);
        });
    }
};
