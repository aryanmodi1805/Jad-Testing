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
        Schema::create('customer_answers', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('request_id');
            $table->foreignUuid('question_id');
            $table->foreignUuid('answer_id');
            $table->double('val')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_answers');
    }
};
