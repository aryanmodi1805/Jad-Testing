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
        // Indexes for seller_services table (used in canBeServedBySeller scope)
        Schema::table('seller_services', function (Blueprint $table) {
            $table->index(['service_id', 'seller_id'], 'idx_seller_services_service_seller');
        });

        // Indexes for seller_service_locations table
        Schema::table('seller_service_locations', function (Blueprint $table) {
            $table->index('seller_service_id', 'idx_seller_service_locations_service');
            $table->index(['seller_service_id', 'is_nationwide'], 'idx_seller_service_locations_nationwide');
        });

        // Indexes for seller_locations table
        Schema::table('seller_locations', function (Blueprint $table) {
            // Index for latitude/longitude lookups
            $table->index(['latitude', 'longitude'], 'idx_seller_locations_lat_lng');
        });

        // Additional indexes on requests table for the complex query
        Schema::table('requests', function (Blueprint $table) {
            // Composite index for status + country filtering
            $table->index(['status', 'country_id'], 'idx_requests_status_country');
            
            // Index for service_id lookups
            $table->index('service_id', 'idx_requests_service');
            
            // Spatial index for latitude/longitude (if using MySQL 5.7+)
            // Note: This requires the columns to be NOT NULL
            // $table->spatialIndex(['latitude', 'longitude'], 'idx_requests_location');
        });

        // Index for seller_request_not_interested table
        Schema::table('seller_request_not_interested', function (Blueprint $table) {
            $table->index(['seller_id', 'request_id'], 'idx_seller_not_interested');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_services', function (Blueprint $table) {
            $table->dropIndex('idx_seller_services_service_seller');
        });

        Schema::table('seller_service_locations', function (Blueprint $table) {
            $table->dropIndex('idx_seller_service_locations_service');
            $table->dropIndex('idx_seller_service_locations_nationwide');
        });

        Schema::table('seller_locations', function (Blueprint $table) {
            $table->dropIndex('idx_seller_locations_lat_lng');
        });

        Schema::table('requests', function (Blueprint $table) {
            $table->dropIndex('idx_requests_status_country');
            $table->dropIndex('idx_requests_service');
        });

        Schema::table('seller_request_not_interested', function (Blueprint $table) {
            $table->dropIndex('idx_seller_not_interested');
        });
    }
};
