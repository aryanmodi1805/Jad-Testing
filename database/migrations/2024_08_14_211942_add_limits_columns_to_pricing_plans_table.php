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
            $table->integer('premium_items_limit')->after('is_unlimited')->default(0)->index();
            $table->integer('credit_items_limit')->after('premium_items_limit')->default(0)->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->dropColumn('premium_items_limit');
            $table->dropColumn('credit_items_limit');
        });
    }
};
