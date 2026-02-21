<?php

use App\Models\Country;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pricing_plans', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->json('name');
            $table->json('description')->nullable();
            $table->tinyInteger('type')->default(0)->comment('2=> Diamond, 1=> gold , 0=> silver , -1=> basic package');
            $table->tinyInteger('billing_cycles')->nullable()->comment(' MONTH,YEAR');
            $table->float('month_price')->nullable()->default(0);
            $table->float('year_price')->nullable()->default(0);
            $table->json('features')->nullable()->comment(' premium plan feature items');
            $table->float('price')->nullable();
            $table->string('currency');
            $table->string('discount')->nullable();
            $table->boolean('ex_VAT')->default(true);
            $table->boolean('is_best_value')->default(false);
            $table->boolean('is_active')->default(true);
            $table->string('tag')->nullable();
            $table->foreignIdFor(Country::class)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pricing_plans');
    }
};
