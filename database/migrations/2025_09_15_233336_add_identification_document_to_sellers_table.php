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
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('identification_document_url')->nullable()->after('cover_image');
            $table->enum('identification_document_status', ['pending', 'approved', 'rejected'])->default('pending')->after('identification_document_url');
            $table->text('identification_document_rejection_reason')->nullable()->after('identification_document_status');
            $table->timestamp('identification_document_verified_at')->nullable()->after('identification_document_rejection_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn([
                'identification_document_url',
                'identification_document_status',
                'identification_document_rejection_reason',
                'identification_document_verified_at'
            ]);
        });
    }
};
