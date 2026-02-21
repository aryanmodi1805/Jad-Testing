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
        Schema::table('customers', function (Blueprint $table) {
            $table->string('locale')->nullable();
        });
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('locale')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('locale')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('locale');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locale');
        });
    }
};
