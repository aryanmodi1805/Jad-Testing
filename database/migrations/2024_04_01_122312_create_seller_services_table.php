<?php

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
        Schema::create('seller_services', function (Blueprint $table) {
            $table->id();
            $table->unique(['seller_id', 'service_id'], 'unique_seller_services');
            $table->foreignIdFor(Seller::class);
            $table->foreignUuid('service_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_services');
    }
};
