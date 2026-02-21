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
            $table->boolean('is_location')->default(0)->after('is_attachment');
            $table->double('latitude')->nullable()->after('is_attachment');
            $table->double('longitude')->nullable()->after('is_attachment');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_answers', function (Blueprint $table) {
            $table->dropColumn('is_location');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }
};
