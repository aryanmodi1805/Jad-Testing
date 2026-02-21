<?php

use App\Models\Customer;
use App\Models\CustomerAnswer;
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
        Schema::create('customer_notification_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Customer::class)->index()->constrained()->cascadeOnDelete();
            $table->boolean('email_new_message')->default(false);
            $table->boolean('email_new_estimate')->default(false);
            $table->boolean('email_new_response')->default(false);
            $table->boolean('email_accepted_invitation')->default(false);
            $table->boolean('email_request_status_change')->default(false);

            $table->boolean('push_new_message')->default(false);
            $table->boolean('push_new_estimate')->default(false);
            $table->boolean('push_new_response')->default(false);
            $table->boolean('push_accepted_invitation')->default(false);
            $table->boolean('push_request_status_change')->default(false);


            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_notification_settings');
    }
};
