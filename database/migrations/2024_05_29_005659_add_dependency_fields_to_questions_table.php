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
        Schema::table('questions', function (Blueprint $table) {
            $table->foreignUuid('dependent_question_id')->nullable()->constrained('questions')->nullOnDelete();
            $table->foreignUuid('dependent_answer_id')->nullable()->constrained('answers')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropForeign(['dependent_question_id']);
            $table->dropColumn('dependent_question_id');
            $table->dropForeign(['dependent_answer_id']);
            $table->dropColumn('dependent_answer_id');
        });
    }
};
