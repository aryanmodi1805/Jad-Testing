<?php

use App\Models\Customer;
use App\Models\CustomerAnswer;
use App\Models\Seller;
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
        Schema::create('seller_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Seller::class)->index()->constrained()->cascadeOnDelete();
            $table->boolean('email_new_message')->default(false);
            $table->boolean('email_invited')->default(false);
            $table->boolean('email_new_request')->default(false);
            $table->boolean('email_rated')->default(false);
            $table->boolean('email_response_status_change')->default(false);

            $table->boolean('push_new_message')->default(false);
            $table->boolean('push_invited')->default(false);
            $table->boolean('push_new_request')->default(false);
            $table->boolean('push_rated')->default(false);
            $table->boolean('push_response_status_change')->default(false);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_notification_settings');
    }
};
