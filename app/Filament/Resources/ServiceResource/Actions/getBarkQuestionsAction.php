<?php

namespace App\Filament\Resources\ServiceResource\Actions;

use App\Enums\AnswerType;
use App\Models\Question;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Http;

class getBarkQuestionsAction extends Action
{

//    protected Model|Closure|null $record = null;


    public static function getDefaultName(): ?string
    {
        return 'get bark Questions';
    }


    protected function setUp(): void
    {
        parent::setUp();


        $this->action(function ($record) {


            $header = array(
                "Content-Type: application/json",
                "x-channel: merchant",
                "Accept: application/json"
            );
            $url = "https://api.bark.com/project-flow";


            $params = [
                'category_id' => $record->bark_id,
                'country_id' => '236',
                'category_slug' => $record->slug,
                'origin' => 'bnb-project-dash',
            ];

            $result = Http::withHeaders($header)
                ->withoutVerifying()
                ->timeout(300)
                ->get($url, $params);

            $body = json_decode($result->body());
            if ($result->status() == 200) {

                $all_data = $result->json('values');
                $all_data = $all_data['categories'][$record->bark_id] ?? [];
                $answer_options = [];
                $sort = 0;
                if ($all_data['custom_fields'])
                {
                    foreach ($all_data['custom_fields'] as $data) {
                        // Find or create the question based on title
                        if ($data['type'] != "postcode") {

                            $question = Question::updateOrCreate(
                                ['label->en' => $data['label'], 'service_id' => $record->id],
                                [
                                    'service_id' => $record->id,
                                    'label' => ['ar' => $data['label'], 'en' => $data['label']],
                                    'type' => $data['type'],
                                    'sort' => $sort,
                                    'has_custom_answer' => isset($data['custom_questions_required']) ? 1 : 0,
                                ]
                            );
                            $sort++;

                            // Process answer options
                            foreach ($data['options'] as $answer) {
                                $is_custom =false;
                                $type = $data['type'];
                                if ($answer['label'] == 'Other')
                                {
                                    $is_custom = true;
                                    $type = 'text';

                                }
                                $answerOption = [
                                    'label' => ['ar' => $answer['label'], 'en' => $answer['label']],
                                    'val' => 0,
                                    'is_custom'=> $is_custom,
                                    'type'=> AnswerType::getType($type),
                                    'image' => $answer['photo'] ?? null,
                                ];

                                // Attach answer option to the question
                                $question->answers()->updateOrCreate(
                                    ['label->en' => $answerOption['label']],
                                    $answerOption
                                );
                            }
                        }
                    }
                    $all_data = [];


                    $this->success();
                    Notification::make()
                        ->title('Successfully')
                        ->success()
                        ->send();
                }else{
                    $this->failure();
                    Notification::make()
                        ->title('No Questions Found')
                        ->danger()
                        ->send();
                }
            }

        })
            ->label(__('Get Bark Questions'));
    }
}
