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
            $table->dropColumn('is_subscribed_all');
            $table->string('premium_type')->nullable()->index()->after('is_premium');
            $table->boolean('is_in_credit')->default(false)->index()->after('premium_type');
            $table->string('credit_type')->nullable()->index()->after('is_in_credit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('premium_type');
            $table->dropColumn('is_in_credit');
            $table->dropColumn('credit_type');
            $table->boolean('is_subscribed_all')->default(false)->index();
        });
    }
};
