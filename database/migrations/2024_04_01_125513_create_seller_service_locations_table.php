<?php

use App\Models\SellerLocation;
use App\Models\SellerService;
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
        Schema::create('seller_service_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_service_id')->constrained('seller_services')->cascadeOnDelete();
            $table->foreignId('seller_location_id')->constrained('seller_locations')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_service_locations');
    }
};
