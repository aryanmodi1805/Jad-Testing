<?php

use App\Models\City;
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
        Schema::create('seller_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Seller::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('location_type')->default('nationwide');
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->integer('range')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_locations');
    }
};
