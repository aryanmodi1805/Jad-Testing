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
        Schema::create('user_countries', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('country_id')->constrained('countries');
            $table->primary(['user_id', 'country_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_countries');
    }
};
