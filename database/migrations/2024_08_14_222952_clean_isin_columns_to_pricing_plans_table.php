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
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->dropColumn('is_in_main_category');
            $table->dropColumn('is_in_sub_category');
            $table->dropColumn('is_in_service');
            $table->dropColumn('main_category_limit');
            $table->dropColumn('sub_category_limit');
            $table->dropColumn('service_limit');
            $table->dropColumn('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            //
            $table->boolean('is_in_main_category')->after('is_unlimited')->default(0)->index();
            $table->boolean('is_in_sub_category')->after('is_in_main_category')->default(0)->index();
            $table->boolean('is_in_service')->after('is_in_sub_category')->default(0)->index();
            $table->integer('main_category_limit')->after('is_in_service')->default(0)->index();
            $table->integer('sub_category_limit')->after('main_category_limit')->default(0)->index();
            $table->integer('service_limit')->after('sub_category_limit')->default(0)->index();
            $table->tinyInteger('type')->after('service_limit')->default(0)->index();

        });
    }
};
