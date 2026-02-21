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
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('previous_tran_ref')->nullable()->after('transaction_id');
            $table->string('refund_response_message')->nullable()->after('previous_tran_ref');
            $table->uuid('parent_id')->nullable()->after('refund_response_message');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchases', function (Blueprint $table) {

            $table->dropColumn('previous_tran_ref');
            $table->dropColumn('refund_response_message');
            $table->dropColumn('parent_id');
        });
    }
};
