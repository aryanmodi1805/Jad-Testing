<?php

use App\Models\Country;
use App\Models\Setting;
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
        Schema::table('settings', function (Blueprint $table) {
            // $table->json('payload')->nullable()->change();


            // $table->string('type')->nullable()->after('payload');
            // $table->string('value')->nullable()->after('payload');
            // $table->boolean('is_active')->default(true)->after('payload');
        });

        // Setting::create([
        //     'name' => 'request_status',
        //     'value' => 1,
        //     'group' => 'request',

        // ]);


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->json('payload')->nullable(false)->change();
            $table->dropColumn(['type', 'value', 'is_active']);
        });    }
};
