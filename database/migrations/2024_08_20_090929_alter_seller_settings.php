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
        DB::statement('SET SESSION sql_mode = "NO_ENGINE_SUBSTITUTION";');

        Schema::table('sellers', function (Blueprint $table) {
            $table->longText('company_name')->after('name')->nullable()->change();
            $table->longText('company_description')->after('name')->nullable()->change();
        });

        Schema::table('seller_profile_services', function (Blueprint $table) {
            $table->longText('service_title')->change();
            $table->longText('service_description')->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->longText('title')->change();
            $table->longText('description')->change();
        });

        Schema::table('seller_q_a_s', function (Blueprint $table) {
            $table->longText('question')->after('id')->change();
            $table->longText('answer')->after('id')->change();
        });

        DB::statement('SET SESSION sql_mode = "STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION";');


    }

    /**
     * Reverse the migrations.
     */

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('company_name')->after('name')->nullable()->change();
            $table->string('company_description')->after('name')->nullable()->change();
        });

        Schema::table('seller_profile_services', function (Blueprint $table) {
            $table->string('service_title')->change();
            $table->string('service_description')->change();
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->string('title')->change();
            $table->string('description')->change();
        });

        Schema::table('seller_q_a_s', function (Blueprint $table) {
            $table->string('question')->after('id')->change();
            $table->string('answer')->after('id')->change();
        });
    }
};
