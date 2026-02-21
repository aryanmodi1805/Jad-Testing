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
        Schema::table('responses', function (Blueprint $table) {
            // Composite index for getSellerResponses query (seller_id + created_at for ordering)
            $table->index(['seller_id', 'created_at'], 'idx_responses_seller_created');
            
            // Index for status filtering
            $table->index(['seller_id', 'status'], 'idx_responses_seller_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responses', function (Blueprint $table) {
            $table->dropIndex('idx_responses_seller_created');
            $table->dropIndex('idx_responses_seller_status');
        });
    }
};
