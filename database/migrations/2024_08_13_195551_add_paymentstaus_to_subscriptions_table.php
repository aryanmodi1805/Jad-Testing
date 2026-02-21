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
            $table->boolean('payment_status')->default(false)->index()->after('trial_ends_at');
            $table->timestamp('canceled_at')->nullable()->after('payment_status');
            $table->index('is_subscribed_all');
            $table->index('is_in_main_category');
            $table->index('is_in_sub_category');
            $table->index('is_in_service');
            $table->index('is_premium');
            $table->index('ends_at');
            $table->index('subscribe_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('payment_status');
            $table->dropColumn('canceled_at');
            $table->dropIndex('is_subscribed_all');
            $table->dropIndex('is_in_main_category');
            $table->dropIndex('is_in_sub_category');
            $table->dropIndex('is_in_service');
            $table->dropIndex('is_premium');
            $table->dropIndex('ends_at');
            $table->dropIndex('subscribe_at');
         });
    }
};
