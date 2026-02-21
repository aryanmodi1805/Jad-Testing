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
            $table->json('voice_note_moderation')->nullable()->after('voice_note');
            $table->text('voice_note_text')->nullable()->after('voice_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_answers', function (Blueprint $table) {
           $table->dropColumn("voice_note_text");
           $table->dropColumn("voice_note_moderation");
        });
    }
};
