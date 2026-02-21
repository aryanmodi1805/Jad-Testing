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
        Schema::create('service_sellers', function (Blueprint $table) {
            $table->primary(['seller_id', 'service_id']);
            $table->foreignIdFor(\App\Models\Seller::class);
            $table->foreignUuid('service_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('service_sellers');
    }
};
