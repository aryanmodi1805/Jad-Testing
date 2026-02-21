<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('settings')->insert([
            'group' => 'app',
            'name' => 'minimum_seller_wallet_balance',
            'locked' => false,
            'payload' => json_encode(0),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settings')
            ->where('group', 'app')
            ->where('name', 'minimum_seller_wallet_balance')
            ->delete();
    }
};
