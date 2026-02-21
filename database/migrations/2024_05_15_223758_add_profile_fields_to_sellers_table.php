<?php

use App\Models\CompanySize;
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
            $table->text('company_description')->after('name')->nullable();
            $table->integer('years_in_business')->after('name')->nullable();
            $table->foreignIdfor(CompanySize::class)->nullable()->after('name')->constrained()->cascadeOnDelete();
            $table->string('location')->after('name')->nullable();
            $table->string('website')->after('name')->nullable();
            $table->string('company_name')->after('name')->nullable();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            //
        });
    }
};
