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
        Schema::create('packages', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('name')->nullable();
            $table->json('description')->nullable();
            $table->integer('credits');
            $table->float('price');
            $table->string('currency');
            $table->string('discount')->nullable();

            $table->boolean('ex_VAT')->default(true);
            $table->boolean('is_best_value')->default(false);
            $table->boolean('is_active')->default(true);
             $table->foreignIdFor(\App\Models\Country::class)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
