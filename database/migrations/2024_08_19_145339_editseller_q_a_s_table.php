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
        Schema::table('seller_q_a_s', function (Blueprint $table) {
            if (Schema::hasColumn('seller_q_a_s', 'q_a_s_id')) {
                $table->dropForeign('seller_q_a_s_q_a_s_id_foreign');
                if (Schema::hasIndex('seller_q_a_s', 'seller_q_a_s_q_a_s_id_foreign')) {
                    $table->dropIndex('seller_q_a_s_q_a_s_id_foreign');
                }
                if (Schema::hasColumn('seller_q_a_s', 'q_a_s_id')) {
                    $table->dropColumn('q_a_s_id');
                }
            }
            $table->text('question')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::table('seller_q_a_s', function (Blueprint $table) {
            $table->dropColumn('question');
        });
    }

};
