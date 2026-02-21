<?php

use App\Models\Seller;
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
        Schema::table('question_suggestions', function (Blueprint $table) {
            $table->foreignIdFor(Seller::class)->nullable()->after('question_id')->constrained();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question_suggestions', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(Seller::class);
        });
    }
};
