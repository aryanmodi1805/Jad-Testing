<?php

use App\Models\Rating;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Rating::all()->each(function ($rating) {
            $rating->update([
                'rating' => intval($rating->rating )
            ]);
        });

    }

};
