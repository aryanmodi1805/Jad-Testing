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
            $table->integer('status')->default(0)->after('seller_id')->index()->comment('0 = pending, 1 = active, 2 = cancelled');            //
            $table->float('total_price')->default(0)->after('status')->index();            //
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscriptions', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->dropColumn('total_price');
        });
    }
};
