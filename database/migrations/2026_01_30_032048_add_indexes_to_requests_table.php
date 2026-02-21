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
        Schema::table('requests', function (Blueprint $table) {
            // Composite index for getUserRequests query (customer_id + created_at for ordering)
            $table->index(['customer_id', 'created_at'], 'idx_requests_customer_created');
            
            // Index for status filtering
            $table->index(['customer_id', 'status'], 'idx_requests_customer_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropIndex('idx_requests_customer_created');
            $table->dropIndex('idx_requests_customer_status');
        });
    }
};
