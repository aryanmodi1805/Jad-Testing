<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customer_answers', function (Blueprint $table) {
            $table->boolean('is_attachment')->default(0)->after('custom_answer');
            $table->text('attachment')->nullable()->after('custom_answer');
            $table->text('voice_note')->nullable()->after('custom_answer');
        });
    }

    public function down(): void
    {
        Schema::table('customer_answers', function (Blueprint $table) {
            $table->dropColumn('is_attachment');
            $table->dropColumn('attachment');
            $table->dropColumn('voice_note');
        });
    }
};
