<?php
namespace App\Services;

use App\Enums\QuestionType;
use App\Enums\RequestStatus;

use App\Filament\Customer\Resources\RequestResource\Pages\ViewRequest;
use App\Jobs\NewRequestNotificationJob;
use App\Models\Customer;
use App\Models\CustomerAnswer;
use App\Models\Question;
use App\Settings\GeneralSettings;
use Filament\Notifications\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;


class RequestService
{
    public mixed $service;

    public mixed $requestId;
    public mixed $request;

    public bool $isAppRequest = false;

    public array $customAnswers;

    final public function __construct(
        public mixed      $service_id,
        public mixed      $countryId,
        public ?Customer   $customer,
        public            $lat,
        public            $lng,
        public            $location_name,
        public Collection $questions,
        public array      $questionAnswers,
        public array      $answersData = [],
        )

    {
    }

    /**
     * @throws \Exception
     */
    function createRequest(Customer $customer = null)
    {
        DB::beginTransaction();
        $this->customer??= $customer;
        try {
            if ($this->service_id) {
                $statusValue = app(GeneralSettings::class)->request_status;
                $statusValue = in_array($statusValue, [0, 1]) ? $statusValue : 0;

                $status = $statusValue == 1 ? RequestStatus::Open : RequestStatus::Pending;
                $hasCustomAnswer = false;
                $this->request = $this->customer->requests()->create([
                    'service_id' => $this->service_id,
                    'latitude' => $this->lat,
                    'longitude' => $this->lng,
                    'location_name' => $this->location_name,
                    'country_id' => $this->countryId,
                    'status' => $status
                ]);

                $this->requestId = $this->request->id;

                $this->customAnswers = $this->answersData['customAnswers'] ?? [];

                foreach ($this->answersData['answers'] ?? [] as $question_id => $answer) {
                    /** @var Question $question */
                    /** @var CustomerAnswer $answer */
                    $question = $this->questions->where('id', $question_id)->first();
                    if($question->is_custom){
                        $hasCustomAnswer = true;
                    }
                    if ($question->type === QuestionType::Checkbox) {
                        $customAnswer = $this->handleCheckboxAnswers($question, $answer);
                        if($customAnswer){
                            $hasCustomAnswer = true;
                        }
                    } elseif ($question->type === QuestionType::SELECT) {
                        $customAnswer = $this->handleSelectAnswer($question, $answer);
                        if($customAnswer){
                            $hasCustomAnswer = true;
                        }
                    } elseif ($question->type === QuestionType::Attachments) {
                        $this->handleAttachmentAnswers($question, $answer);
                    } elseif ($question->type === QuestionType::PreciseDate) {
                        $this->handlePreciseDateAnswers($question, $answer);
                    } elseif ($question->type === QuestionType::DateRange) {
                        $this->handleDateRangeAnswers($question, $answer);
                    } elseif (in_array($question->type, [QuestionType::Text, QuestionType::TextArea, QuestionType::Number, QuestionType::Date])) {
                        $this->handleTextAnswers($question, $answer);
                    } elseif ($question->type === QuestionType::Location) {
                        $this->handleLocationAnswers($question, $answer);
                    } else {
                        $this->handleOtherAnswers($question, $answer);
                    }
                }

                DB::commit();

                if($hasCustomAnswer && $status == RequestStatus::Open){
                    $this->request->status = RequestStatus::Pending;
                    $this->request->saveQuietly();
                }

                if ($status == RequestStatus::Open) {
                    NewRequestNotificationJob::dispatch($this->request)->delay(5000)->afterResponse();
                }

                if(!$this->isAppRequest) {
                    Notification::make()->title(__('string.create_request_success'))->success()->send();

                    if (\Session::has('pending_request')) \Session::forget('pending_request');


                    return redirect(ViewRequest::getUrl([
                        'record' => $this->request,
                        'tenant' => getCurrentTenant()
                    ], panel: 'customer'));
                }else{
                    return $this->request;
                }
            }

            abort(404);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting request: ' . $e->getMessage());
            throw $e;
        }

    }


    /**
     * @throws \Exception
     */

    //Formatting the application answers to be used in creating request
    function createAppRequest(Request $request)
    {
        $this->isAppRequest = true;

        $customAnswers = [];

        $answers = [];

        foreach ($this->answersData as $question_id => $answer) {
            $question = $this->questions->where('id', $question_id)->first();

            if(isset($answer['custom_answer'])) {
                $customAnswers[$question_id] = $answer['custom_answer'];
            }

            if ($question->type === QuestionType::Checkbox && isset($answer['answer_ids']) && count($answer['answer_ids']) > 0) {
                $answers[$question_id] = $answer['answer_ids'];

            } elseif ($question->type === QuestionType::SELECT && isset($answer['answer_id'])) {
                $answers[$question_id] = $answer['answer_id'];

            } elseif ($question->type === QuestionType::Attachments) {
                if(isset($answer['attachments'])) {
                    foreach ($answer['attachments'] as $key => $attachment) {
                        $file = $this->handleAppFiles($request, $attachment);
                        if($file != null){
                            $answers[$question_id]['attachments'][$key] = $file;
                        }
                    }
                }
                if(isset($answer['voice_note'])) {
                    $answers[$question_id]['voice_note'] = $this->handleAppFiles($request, $answer['voice_note']) ;

                }

            } elseif ($question->type === QuestionType::PreciseDate && isset($answer['text_answer'], $answer['time'], $answer['duration'], $answer['duration_type'])) {
                $answers[$question_id] = [
                    'date' => $answer['text_answer'],
                    'time' => $answer['time'],
                    'duration' => $answer['duration'],
                    'duration_type' => $answer['duration_type'],
                ];

            } elseif ($question->type === QuestionType::DateRange && isset($answer['start_date'], $answer['end_date'])) {
                $answers[$question_id] = $answer['start_date'] . ' - ' . $answer['end_date'];
            } elseif (in_array($question->type, [QuestionType::Text, QuestionType::TextArea, QuestionType::Number, QuestionType::Date]) && isset($answer['text_answer'])) {
                $answers[$question_id] = $answer['text_answer'];
            } elseif ($question->type === QuestionType::Location && isset($answer['text_answer'], $answer['latitude'], $answer['longitude'])) {
                    $answers[$question_id] = [
                        'location_name' => $answer['text_answer'],
                        'location' => [
                            'lat' => $answer['latitude'],
                            'lng' => $answer['longitude'],
                        ]
                    ];
            }



        }
        $this->answersData = [
            'answers' => $answers,
            'customAnswers' => $customAnswers
        ];

        return $this->createRequest();
    }

    protected function handleAppFiles($request, $selectedAnswer)
    {
        $requestFiles = $request->allFiles()['files'];

        foreach ($requestFiles as $file) {
            if ($file->getClientOriginalName() == $selectedAnswer) {
                return $file;
            }
        }

        return null;

    }

    protected function handlePreciseDateAnswers(Question $question, $selectedAnswer): void
    {
        if (isset($selectedAnswer['date'], $selectedAnswer['time'], $selectedAnswer['duration'], $selectedAnswer['duration_type'])) {

            $data = [
                'request_id' => $this->requestId,
                'question_id' => $question->id,
                 'question_label' => $question->getTranslations('label'),
                'question_type' => $question->type,
                'question_sort' => $question->sort,
                'text_answer' => $selectedAnswer['date'],
                'time' => $selectedAnswer['time'],
                'duration' => $selectedAnswer['duration'],
                'duration_type' => $selectedAnswer['duration_type'],
                'is_custom' => $question->is_custom,
                'val' => $question->val,
            ];

            CustomerAnswer::create($data);
        }
    }
    protected function handleDateRangeAnswers($question, $selectedAnswer): void
    {
        if (isset($selectedAnswer)) {
            $data = [
                'request_id' => $this->requestId,
                'question_id' => $question->id,
                 'question_label' => $question->getTranslations('label'),
                'question_type' => $question->type,
                'question_sort' => $question->sort,
                'text_answer' => $selectedAnswer,
                'is_custom' => $question->is_custom,
                'val' => $question->val,
            ];

            CustomerAnswer::create($data);
        }
    }
    protected function handleCheckboxAnswers($question, $selectedAnswers): bool
    {
        $answers = $this->questionAnswers[$question->id];
        $customAnswer = $this->customAnswers[$question->id] ?? null;
        $hasCustomAnswer = false;

        foreach ($selectedAnswers as $answer_id) {
            $answer = $answers->find($answer_id);
            if (!$answer) {
                continue;
            }

            $data = [
                'request_id' => $this->requestId,
                'question_id' => $question->id,
                 'question_label' => $question->getTranslations('label'),
                'question_type' => $question->type,
                'question_sort' => $question->sort,
                'answer_label' => $answer->getTranslations('label'),
                'answer_id' => $answer_id,
                'val' => $answer->val,
            ];

            if ($answer->is_custom && $customAnswer !== null) {
                $data['is_custom'] = true;
                $data['custom_answer'] = $this->customAnswers[$question->id];
                $hasCustomAnswer = true;
            } else {
                $data['is_custom'] = false;
                $data['custom_answer'] = null;
            }

            CustomerAnswer::create($data);

        }

        return $hasCustomAnswer;
    }
    protected function handleSelectAnswer($question, $selectedAnswer): bool
    {
        $answers = $this->questionAnswers[$question->id];
        $customAnswer = $this->customAnswers[$question->id] ?? null;
        $hasCustomAnswer = false;


        if(filled($selectedAnswer)) {

            $answer = $answers->find($selectedAnswer);

            if (!$answer) {
                return false;
            }

            $data = [
                'request_id' => $this->requestId,
                'question_id' => $question->id,
                 'question_label' => $question->getTranslations('label'),
                'question_type' => $question->type,
                'question_sort' => $question->sort,
                'answer_label' => $answer->getTranslations('label'),
                'answer_id' => $answer->id,
                'val' => $answer->val,
            ];

            if ($answer->is_custom && $customAnswer !== null) {
                $data['is_custom'] = true;
                $data['custom_answer'] = $this->customAnswers[$question->id];
                $hasCustomAnswer = true;

            } else {
                $data['is_custom'] = false;
                $data['custom_answer'] = null;
            }

            CustomerAnswer::create($data);
        }
        return $hasCustomAnswer;
    }
    protected function handleAttachmentAnswers($question, $selectedAnswer): void
    {

        $data = [
            'request_id' => $this->requestId,
            'question_id' => $question->id,
             'question_label' => $question->getTranslations('label'),
            'question_type' => $question->type,
            'question_sort' => $question->sort,

            'is_custom' => $question->is_custom,
            'val' => $question->val,
        ];

        if(isset($selectedAnswer['attachments'])){
            $attachments = $this->handleFileUploads($selectedAnswer['attachments']);

            $data['is_attachment'] = true;
            $data['attachment'] = $attachments;
        }else{
            $data['is_attachment'] = false;
            $data['attachment'] = null;
        }

        if(isset($selectedAnswer['voice_note'])) {
            if($selectedAnswer['voice_note'] instanceof UploadedFile){
                $file = $selectedAnswer['voice_note'];
                $voiceNotePath =  $this->processVoiceNoteFile($file);
            }else{
                $voiceNotePath = $this->processVoiceNote($selectedAnswer['voice_note']);
            }
            $data['voice_note'] = $voiceNotePath;

        }else{
            $data['voice_note'] = null;
        }

        CustomerAnswer::create($data);
    }

    protected function handleTextAnswers($question, $selectedAnswer): void
    {
        if(filled($selectedAnswer)){
            $data = [
                'request_id' => $this->requestId,
                'question_id' => $question->id,
                 'question_label' => $question->getTranslations('label'),
                'question_type' => $question->type,
                'question_sort' => $question->sort,

                'text_answer' => $selectedAnswer,
                'is_custom' => $question->is_custom,
                'val' => $question->val,
            ];

            CustomerAnswer::create($data);
        }
    }

    protected function handleLocationAnswers($question, $selectedAnswer): void
    {
        if (isset($selectedAnswer['location_name'], $selectedAnswer['location']['lat'] , $selectedAnswer['location']['lng'])) {
            $data = [
                'request_id' => $this->requestId,
                'question_id' => $question->id,
                 'question_label' => $question->getTranslations('label'),
                'question_type' => $question->type,
                'question_sort' => $question->sort,

                'text_answer' => $selectedAnswer['location_name'],
                'latitude' => $selectedAnswer['location']['lat'],
                'longitude' => $selectedAnswer['location']['lng'],
                'is_custom' => $question->is_custom,
                'val' => $question->val,
            ];

            CustomerAnswer::create($data);
        }
    }

    protected function handleOtherAnswers(Question $question, $selectedAnswer): void
    {
        $answer = $question->answers->find($selectedAnswer);
        if (!$answer) {
            return;
        }

        $data = [
            'request_id' => $this->requestId,
            'question_id' => $question->id,
            'question_label' => $question->getTranslations('label'),
            'question_type' => $question->type,
            'question_sort' => $question->sort,
            'answer_id' => $answer->id,
            'val' => $answer->val,
        ];

        if ($answer->is_custom && isset($this->customAnswers[$answer->id])) {
            $data['is_custom'] = true;
            $data['custom_answer'] = $this->customAnswers[$answer->id];
        } else {
            $data['is_custom'] = false;
        }

        CustomerAnswer::create($data);
    }

    protected function handleFileUploads($files): array
    {
        $attachments = [];



        if (is_array($files)) {
            foreach ($files as $key => $file) {
                if($file instanceof UploadedFile) {
                    $attachments[] = [
                        'path' => $file->store('attachments', 'public'),
                        'name' => $key,
                    ];
                }else{
                    if(file_exists(storage_path('app/public/' . $file))){
                        $uploadedFile = new UploadedFile(storage_path('app/public/' . $file), $file);

                        $attachments[] = [
                            'path' => $uploadedFile->store('attachments', 'public'),
                            'name' => $key,
                        ];
                    }
                }


            }
        }

        return $attachments;
    }

    protected function processVoiceNote($base64VoiceNote): ?string
    {
        if (isset($base64VoiceNote) && preg_match('/^data:audio\/\w+;base64,/', $base64VoiceNote)) {
            $audioData = base64_decode(preg_replace('#^data:audio/\w+;base64,#i', '', $base64VoiceNote));
            $fileName = uniqid('audio_') . '.m4a';
            $filePath = 'voiceNotes/' . $fileName;
            Storage::disk('public')->put($filePath, $audioData);
            return $filePath;
        }
        return null;
    }

    protected function processVoiceNoteFile(UploadedFile $file): ?string
    {
        if($file->isValid()){
            $fileName = uniqid('audio_') . '.m4a';
            $file->storeAs('voiceNotes',$fileName, 'public');
            return 'voiceNotes/' . $fileName;
        }

        return null;
    }

}

