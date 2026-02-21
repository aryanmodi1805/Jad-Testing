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
            $table->longText('attachments')->nullable();
            $table->longText('message')->nullable()->change();
            $table->string('original_attachment_file_names')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('attachments');
            $table->dropColumn('original_attachment_file_names');

            $table->string('message')->nullable(false)->change();
        });
    }
};
