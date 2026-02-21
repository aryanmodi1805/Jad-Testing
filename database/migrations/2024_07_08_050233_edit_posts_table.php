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
        Schema::table('posts', function (Blueprint $table) {
            $table->boolean('is_static')->default(false)->after('status');
            $table->boolean('show_footer')->default(false)->after('is_static');
            $table->boolean('show_header')->default(false)->after('is_static');
            $table->unsignedBigInteger('footer_tag_id')->nullable()->after('is_static');
         });
         Schema::table('tags', function (Blueprint $table) {
            $table->boolean('show_footer')->default(false)->after('type');
            $table->boolean('is_static')->default(false)->after('type');

         });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn('is_static');
            $table->dropColumn('show_footer');
            $table->dropColumn('show_header');
            $table->dropColumn('footer_tag_id');
        });
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn('show_footer');
            $table->dropColumn('is_static');
        });
    }
};
