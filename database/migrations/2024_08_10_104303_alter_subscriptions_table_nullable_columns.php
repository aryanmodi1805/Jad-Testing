<?php

use App\Models\PricingPlan;
use App\Models\Seller;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {


        Schema::table('subscriptions', function (Blueprint $table) {

            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->string('type')->nullable()->change();
            $table->string('stripe_id')->nullable()->change();
            $table->string('stripe_status')->nullable()->change();
            $table->timestamp('subscribe_at')->nullable();
            /*==================================================================*/
            $table->foreignIdFor(PricingPlan::class, 'price_plan_id')->nullable()->index()->after('user_id');
            $table->foreignIdFor(Seller::class, 'seller_id')->index()->after('price_plan_id');
            /*==================================================================*/
            $table->boolean('is_subscribed_all')->default(false)->after('seller_id');
            $table->boolean('is_in_main_category')->default(false)->after('is_subscribed_all');
            $table->boolean('is_in_sub_category')->default(false)->after('is_in_main_category');
            $table->boolean('is_in_service')->default(false)->after('is_in_sub_category');

            $table->boolean('is_premium')->default(false)->after('is_in_service');
            /*==================================================================*/
            $table->boolean('is_auto_renew')->default(false)->after('is_premium');

            $table->boolean('is_yearly')->default(false)->after('is_auto_renew');
            $table->boolean('is_monthly')->default(false)->after('is_yearly');
            $table->timestamp('renew_at')->nullable()->after('is_monthly');
            $table->timestamp('next_renew_at')->nullable();

            $table->string('payment_method_id')->nullable();
            $table->longText('payment_details')->nullable();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

        Schema::table('subscriptions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->string('type')->nullable(false)->change();
            $table->string('stripe_id')->nullable(false)->change();
            $table->string('stripe_status')->nullable(false)->change();

            $table->dropColumn(
                ['is_subscribed_all',
                'is_in_main_category',
                'is_in_sub_category',
                'is_premium',
                'is_auto_renew',
                'is_yearly',
                'is_monthly',
                'subscribe_at',
                'next_renew_at',
                'renew_at',
                'payment_method_id',
                'payment_details',
                'price_plan_id',
                'seller_id']);
        });
    }
};
