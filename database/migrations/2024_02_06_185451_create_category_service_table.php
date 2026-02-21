<?php

use App\Models\Category;
use App\Models\Service;
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
        Schema::create('category_service', function (Blueprint $table) {
            $table->primary(['category_id', 'service_id']);
            $table->foreignIdFor(Category::class)->index();
            $table->foreignIdFor(Service::class)->index();
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_service');
    }
};
