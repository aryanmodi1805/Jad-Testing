<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use O21\LaravelWallet\Models\Transaction;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuidMorphs('payable');
            $table->uuidMorphs('purchasable');
            $table->decimal('amount', 8, 2);
            $table->tinyInteger('status')->default(0);
            $table->boolean('is_form_wallet')->default(0);
            $table->foreignIdFor(Transaction::class)->nullable();
            $table->string('payment')->nullable();


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
