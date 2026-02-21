<?php

use App\Models\Customer;
use App\Models\Seller;
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
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignIdFor(Seller::class)->nullable()->after('id');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->foreignIdFor(Customer::class)->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('seller_id');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('customer_id');
        });
    }
};
