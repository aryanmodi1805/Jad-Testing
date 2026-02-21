<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->boolean('blocked')->after('email_verified_at')->default(false);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('blocked')->after('email_verified_at')->default(false);
        });
    }

    public function down()
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn('blocked');
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('blocked');
        });
    }
};
