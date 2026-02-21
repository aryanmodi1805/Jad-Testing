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
        Schema::create('payment_details', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\PaymentMethod::class)->index();
            $table->foreignIdFor(\App\Models\Purchase::class)->index()->nullable();
            $table->foreignIdFor(\App\Models\Seller::class)->index()->nullable();
            $table->json('payment_details')->nullable();
            $table->json('otp_details')->nullable();
            $table->json('validate_details')->nullable();
            $table->text('refund_reason')->nullable();
            $table->string('file')->nullable();
            $table->boolean('is_refund')->default(false);
            $table->double('amount')->default(0);
            $table->tinyInteger('status')->default(0);
            $table->text('cancel_reason')->nullable();
            $table->foreignIdFor(\App\Models\User::class,'confirmed_by')->nullable()->index();
            $table->foreignIdFor(\App\Models\Country::class)->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_details');
    }
};
