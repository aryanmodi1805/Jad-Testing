<?php

use App\Models\Country;
use App\Models\PaymentDetail;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('purchases', function (Blueprint $table) {

            $table->string('currency')->nullable()->after('transaction_id');
            $table->foreignIdFor(PaymentDetail::class, 'payment_detail_id')->nullable()->after('currency');
            $table->foreignIdFor(Country::class, 'country_id')->nullable()->after('payment_detail_id');
            //is_refund
            $table->boolean('is_refund')->default(false)->after('country_id');
            $table->float('refund_amount')->nullable()->after('is_refund');
            $table->boolean('request_refund')->default(false)->after('refund_amount');
            $table->nullableMorphs('chargeable');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {

            $table->dropColumn('currency');
            $table->dropColumn('payment_detail_id');
            $table->dropColumn('country_id');
            $table->dropColumn('is_refund');
            $table->dropColumn('refund_amount');
            $table->dropColumn('request_refund');
            $table->dropMorphs('chargeable');
        });
    }
};
