<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Handle duplicate phone numbers in customers table
        DB::statement("
            UPDATE customers c1
            INNER JOIN (
                SELECT phone, MIN(id) as min_id
                FROM customers 
                WHERE phone IS NOT NULL AND phone != ''
                GROUP BY phone 
                HAVING COUNT(*) > 1
            ) c2 ON c1.phone = c2.phone AND c1.id != c2.min_id
            SET c1.phone = NULL
        ");

        // Handle duplicate phone numbers in sellers table
        DB::statement("
            UPDATE sellers s1
            INNER JOIN (
                SELECT phone, MIN(id) as min_id
                FROM sellers 
                WHERE phone IS NOT NULL AND phone != ''
                GROUP BY phone 
                HAVING COUNT(*) > 1
            ) s2 ON s1.phone = s2.phone AND s1.id != s2.min_id
            SET s1.phone = NULL
        ");

        // Add unique constraints after cleaning duplicates
        Schema::table('customers', function (Blueprint $table) {
            $table->unique('phone');
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->unique('phone');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropUnique(['phone']);
        });

        Schema::table('sellers', function (Blueprint $table) {
            $table->dropUnique(['phone']);
        });
    }
};
