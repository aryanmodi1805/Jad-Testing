<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration modifies the pending_payments table to support both
     * sellers and customers by:
     * 1. Removing the foreign key constraint to sellers table
     * 2. Adding a user_type column to distinguish between 'seller' and 'customer'
     */
    public function up(): void
    {
        Schema::table('pending_payments', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['user_id']);
            
            // Add user_type column after user_id to distinguish seller vs customer
            $table->string('user_type', 20)->default('seller')->after('user_id');
            
            // Add index for the combined user lookup
            $table->index(['user_id', 'user_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pending_payments', function (Blueprint $table) {
            // Drop the new index
            $table->dropIndex(['user_id', 'user_type']);
            
            // Remove user_type column
            $table->dropColumn('user_type');
            
            // Re-add the foreign key constraint (only works if all user_ids are valid seller IDs)
            $table->foreign('user_id')->references('id')->on('sellers')->onDelete('cascade');
        });
    }
};
