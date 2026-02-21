<?php

use App\Models\Currency;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            $table->dropColumn('currency');
            $table->foreignIdFor(Currency::class)->nullable();
        });
        Schema::table('pricing_plans', function (Blueprint $table) {
            $table->dropColumn('currency');
            $table->foreignIdFor(Currency::class)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('packages', function (Blueprint $table) {
            if (!Schema::hasColumn('packages','currency')) {
                $table->string('currency')->nullable();
            }

            $table->dropColumn('currency_id');
        });
        Schema::table('pricing_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('pricing_plans','currency')) {
                $table->string('currency')->nullable();
            }
            $table->dropColumn('currency_id');
        });
    }
};
