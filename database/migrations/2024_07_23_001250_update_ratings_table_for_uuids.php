<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateRatingsTableForUuids extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->uuid('rateable_id')->change();
            $table->string('rateable_type')->change();
            $table->uuid('rater_id')->change();
            $table->string('rater_type')->change();

            $table->index(['rateable_id', 'rateable_type']);
            $table->index(['rater_id', 'rater_type']);
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ratings', function (Blueprint $table) {
            $table->unsignedBigInteger('rateable_id')->change();
            $table->string('rateable_type')->change();
            $table->unsignedBigInteger('rater_id')->change();
            $table->string('rater_type')->change();
        });
    }
}
