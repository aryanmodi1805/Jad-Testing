<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->foreignUuid('response_id')->nullable()->after('id')->constrained('responses')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->dropForeign(['response_id']);
            $table->dropColumn('response_id');
        });
    }
};
