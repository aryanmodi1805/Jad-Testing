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
        Schema::table('messages', function (Blueprint $table) {
            $table->boolean('notified')->default(false)->index();
            $table->index(['sender_id', 'sender_type']);
            $table->index('read_at');
            $table->index('response_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('notified');
            $table->dropIndex(['sender_id', 'sender_type']);
            $table->dropIndex('read_at');
            $table->dropIndex('response_id');
        });
    }
};
