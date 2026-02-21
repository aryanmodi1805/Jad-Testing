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
        Schema::create('activities', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('response_id')->constrained('responses')->onDelete('cascade');
            $table->string('type');  // 'call', 'WhatsApp', 'email', 'SMS'
            $table->string('status');  //  'no answer', 'spoke with person', 'left voicemail', 'number not on WhatsApp'
            $table->text('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activities');
    }
};
