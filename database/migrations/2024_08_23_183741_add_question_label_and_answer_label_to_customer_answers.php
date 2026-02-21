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
            $table->longText('question_label')->nullable()->after('question_id');
            $table->longText('answer_label')->nullable()->after('answer_id');
            $table->string('question_type')->nullable()->after('question_id');
            $table->string('question_sort')->nullable()->after('question_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_answers', function (Blueprint $table) {
            $table->dropColumn('question_label');
            $table->dropColumn('answer_label');
            $table->dropColumn('question_type');
            $table->dropColumn('question_sort');
        });
    }
};
