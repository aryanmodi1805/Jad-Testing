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
        Schema::table('subscription_items', function (Blueprint $table) {
            $table->index('main_category_id');
            $table->index('sub_category_id');
            $table->index('service_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            $table->dropIndex('main_category_id');
            $table->dropIndex('sub_category_id');
            $table->dropIndex('service_id');
        });
    }
};
