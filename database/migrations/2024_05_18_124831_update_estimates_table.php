<?php

use App\Models\EstimateBase;
use App\Settings\EstimateSettings;
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
        Schema::table('estimates', function (Blueprint $table) {
            $table->dropColumn('basis');

            $table->foreignIdFor(EstimateBase::class)->after('amount')->constrained()->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('estimates', function (Blueprint $table) {
            $table->string('basis');
            $table->dropConstrainedForeignId('estimate_base_id');
//            $table->dropColumn('estimate_base_id');
        });
    }
};
