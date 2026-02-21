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
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('is_in_main_category');
            $table->dropColumn('is_in_sub_category');
            $table->dropColumn('is_in_service');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->boolean('is_in_main_category')->after('is_unlimited')->default(0)->index();
            $table->boolean('is_in_sub_category')->after('is_in_main_category')->default(0)->index();
            $table->boolean('is_in_service')->after('is_in_sub_category')->default(0)->index();
        });
    }
};
