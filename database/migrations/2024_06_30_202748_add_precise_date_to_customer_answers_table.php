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
        Schema::table('customer_answers', function (Blueprint $table) {
            $table->time('time')->nullable()->after('is_location');
            $table->integer('duration')->nullable()->after('time');
            $table->enum('duration_type', ['minutes', 'hours', 'days'])->nullable()->after('duration');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_answers', function (Blueprint $table) {
            $table->dropColumn('time');
            $table->dropColumn('duration');
            $table->dropColumn('duration_type');
        });
    }
};
