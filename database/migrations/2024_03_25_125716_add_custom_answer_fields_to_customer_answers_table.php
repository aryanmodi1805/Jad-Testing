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
            $table->boolean('is_custom')->default(false)->after('answer_id');
            $table->string('custom_answer')->nullable()->after('is_custom');
            $table->foreignUuid('answer_id')->nullable()->change();


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_answers', function (Blueprint $table) {
            $table->dropColumn('is_custom');
            $table->dropColumn('custom_answer');
            $table->foreignUuid('answer_id')->change();

        });
    }
};
