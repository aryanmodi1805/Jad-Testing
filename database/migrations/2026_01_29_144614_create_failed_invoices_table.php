<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('failed_invoices', function (Blueprint $table) {
            $table->id();
            $table->morphs('invoiceable'); // user_id + user_type (User/Seller)
            $table->string('transaction_id')->index();
            $table->string('invoice_type'); // wallet_recharge, service_payment
            $table->decimal('amount', 10, 2);
            $table->string('currency', 10)->default('SAR');
            $table->text('error_message');
            $table->json('error_details')->nullable();
            $table->json('request_payload')->nullable(); // Store what was sent to Wafeq
            $table->boolean('resolved')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('failed_invoices');
    }
};
