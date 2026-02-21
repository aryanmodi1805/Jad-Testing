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
            'name' => 'maximum_requests_per_day',
            'locked' => false,
            'payload' => json_encode(5),
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
            ->where('name', 'maximum_requests_per_day')
            ->delete();
    }
};
