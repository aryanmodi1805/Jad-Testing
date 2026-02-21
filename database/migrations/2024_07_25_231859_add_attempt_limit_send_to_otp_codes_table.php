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
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->unsignedInteger('failed_attempts')->default(0)->after('expires_at')->index();
            $table->unsignedInteger('send_attempts')->default(0)->after('failed_attempts')->index();
            $table->timestamp('cooldown_start')->nullable()->after('send_attempts')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->dropColumn('failed_attempts');
            $table->dropColumn('send_attempts');
            $table->dropColumn('cooldown_start');
        });
    }
};
