<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->nullableUuidMorphs('notifiable');
            $table->timestamp('cooldown_end')->after('cooldown_start')->nullable()->index();
            $table->string('channel')->after('cooldown_end')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('otp_codes', function (Blueprint $table) {
            $table->dropMorphs('notifiable');
            $table->dropColumn('cooldown_end');
            $table->dropColumn('channel');
        });
    }
};
