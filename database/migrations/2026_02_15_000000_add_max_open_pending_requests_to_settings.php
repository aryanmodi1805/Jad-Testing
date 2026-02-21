<?php

use Illuminate\Database\Migrations\Migration;
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
            'name' => 'max_open_pending_requests',
            'locked' => false,
            'payload' => json_encode(2),
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
            ->where('name', 'max_open_pending_requests')
            ->delete();
    }
};
