<?php

use App\Models\Answer;
use App\Models\CustomerAnswer;
use App\Models\Question;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    /* QuestionType Old Values
        case SELECT = 'select';
        case Checkbox = 'checkbox';
        case Date = 'date';
        case Number = 'number';
        case TextArea = 'textarea';
        case Text = 'text';
        case Attachments = 'attachments';
        case Location = 'location';
        case PreciseDate = 'precise_date';
        case DateRange = 'date_range';
     * */

    /* QuestionType New Values
        case SELECT = 1;
        case Checkbox = 2;
        case Date = 3;
        case Number = 4;
        case TextArea = 5;
        case Text = 6;
        case Attachments = 7;
        case Location = 8;
        case PreciseDate = 9;
        case DateRange = 10;
     * */

    public function up(): void
    {
        Schema::table('customer_answers', function (Blueprint $table) {
            $table->renameColumn('question_type', 'old_type');
            $table->integer('question_type')->after('question_id')->nullable();
        });

        // Convert Question Type to Int

        foreach (CustomerAnswer::all() as $question) {
            $question->question_type = match ($question->old_type) {
                'select' => 1,
                'checkbox' => 2,
                'date' => 3,
                'number' => 4,
                'textarea' => 5,
                'text' => 6,
                'attachments' => 7,
                'location' => 8,
                'precise_date' => 9,
                'date_range' => 10,
                default => null,
            };
            $question->save();
        }

        Schema::table('customer_answers', function (Blueprint $table) {
            $table->dropColumn('old_type');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_answers', function (Blueprint $table) {
            $table->renameColumn('question_type', 'old_type');
            $table->string('question_type')->after('label')->nullable();
        });

        // Convert Question Type to String

        foreach (Question::all() as $question) {
            $question->question_type = match ($question->old_type) {
                1 => 'select',
                2 => 'checkbox',
                3 => 'date',
                4 => 'number',
                5 => 'textarea',
                6 => 'text',
                7 => 'attachments',
                8 => 'location',
                9 => 'precise_date',
                10 => 'date_range',
                default => null,
            };
            $question->save();
        }

        Schema::table('customer_answers', function (Blueprint $table) {
            $table->dropColumn('old_type');
        });
    }
};
