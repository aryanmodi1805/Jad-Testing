<?php

use App\Models\QA;
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
        Schema::table('seller_q_a_s', function (Blueprint $table) {
            $table->dropColumn('question');
            $table->foreignId('q_a_s_id')->after('id')->constrained('q_a_s')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_q_a_s', function (Blueprint $table) {
            $table->string('question')->after('id');
            $table->dropConstrainedForeignId('q_a_id');
        });
    }
};
