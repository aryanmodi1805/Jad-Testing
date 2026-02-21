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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->longText('name')->change();
            $table->string('icon')->nullable()->after('logo');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
            // drop column icon if exist before
            if (Schema::hasColumn('payment_methods', 'icon')) {
                $table->dropColumn('icon');
            }
        });
    }
};
