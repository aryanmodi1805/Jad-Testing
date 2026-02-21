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
             $table->string('stripe_id')->nullable()->change();
            $table->string('stripe_product')->nullable()->change();
            $table->timestamp('stripe_price')->nullable()->change();
            /*==================================================================*/
            $table->foreignUuid('main_category_id') ->after('subscription_id')->nullable();
            $table->foreignUuid('sub_category_id')->after('main_category_id')->nullable();
            $table->foreignUuid('service_id') ->after('sub_category_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_items', function (Blueprint $table) {
            $table->dropColumn(['main_category_id', 'sub_category_id', 'service_id']);
        });
    }
};
