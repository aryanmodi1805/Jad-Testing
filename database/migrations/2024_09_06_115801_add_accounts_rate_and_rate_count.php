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
        Schema::table('sellers', function (Blueprint $table) {
            $table->float('rate')->default(0);
            $table->integer('rate_count')->default(0);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->float('rate')->default(0);
            $table->integer('rate_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('rate');
            $table->dropColumn('rate_count');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('rate');
            $table->dropColumn('rate_count');
        });
    }
};
