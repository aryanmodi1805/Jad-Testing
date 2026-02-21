<?php

use App\Models\BlockReason;
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
        Schema::create('block_reports', function (Blueprint $table) {
            $table->id();
            $table->nullableUuidMorphs('reference');
            $table->morphs('blocker');
            $table->morphs('blocked');
            $table->foreignIdFor(BlockReason::class)->nullable();
            $table->longText('details')->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('block_reports');
    }
};
