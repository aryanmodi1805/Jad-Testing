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
        Schema::create('pending_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('sellers')->onDelete('cascade');
            $table->string('charge_id')->unique(); // Tap charge ID
            $table->uuid('response_id')->nullable(); // For service payments
            $table->string('payment_type'); // 'service_payment', 'credit_charge', 'subscription'
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('SAR');
            $table->enum('status', ['pending', 'verifying', 'completed', 'failed', 'expired'])->default('pending');
            $table->integer('verification_attempts')->default(0);
            $table->timestamp('last_verified_at')->nullable();
            $table->timestamp('expires_at');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['charge_id', 'status']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_payments');
    }
};
