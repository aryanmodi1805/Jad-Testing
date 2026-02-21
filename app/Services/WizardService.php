<?php
namespace App\Services;

use App\Enums\AnswerType;
use App\Enums\QuestionType;
use App\Filament\Customer\Resources\RequestResource\Pages\ListRequests;
use App\Forms\Components\VoiceNote;
use App\Interfaces\HasWizard;
use App\Models\Question;
use App\Models\Service;
use App\Rules\FileNameNoScripts;
use App\Rules\LatLngInCountry;
use Carbon\Carbon;
use App\Filament\Components\Geocomplete;
use App\Filament\Components\Map;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Wizard\Step;
use Filament\Notifications\Notification;
use HusamTariq\FilamentTimePicker\Forms\Components\TimePickerField;
use Illuminate\Support\Collection;
use Malzariey\FilamentDaterangepickerFilter\Fields\DateRangePicker;


class WizardService
{
    final public function __construct(
        public mixed $service_id = null,
        public $lat = null,
        public $lng = null,
        public $location_name = null,
    ){
    }

    public Collection $questions;

    public array $questionAnswers;

    public array $answers;
    public array $questionHasDependentQuestions;
    public array $questionHasCustomAnswer;

    public function getWizardData() : array{

        $service = Service::with([
            'questions.answers',
            'questions' => fn($query) => $query->withCount('dependentQuestions')->orderBy('sort'),
            'questions.dependentQuestions',

        ])->findOrFail($this->service_id);

        $questions = $service->questions;

        $questionAnswers = [];

        $questionHasDependentQuestions = [];

        $questionHasCustomAnswer = [];

        foreach ($questions as $question) {

            $questionAnswers[$question->id] = $question->answers;

            if($question->dependent_questions_count > 0){
                $questionHasDependentQuestions[$question->id] = true;

            }else{
                $questionHasDependentQuestions[$question->id] = false;
            }

            $questionHasCustomAnswer[$question->id] = false;

            foreach ($question->answers as $answer) {
                if ($answer->has_another_input) {
                    $questionHasCustomAnswer[$question->id] = true;
                }
            }
        }

        return [
            'service' => $service,
            'questions' => $questions,
            'questionAnswers' => $questionAnswers,
            'questionHasDependentQuestions' => $questionHasDependentQuestions,
            'questionHasCustomAnswer' => $questionHasCustomAnswer,
        ];

    }
    public function getWizardSteps($data): array
    {
        $this->questions = $data['questions'] ?? Collect();
        $this->questionAnswers = $data['questionAnswers'] ?? [];
        $this->questionHasDependentQuestions = $data['questionHasDependentQuestions'] ?? [];
        $this->questionHasCustomAnswer = $data['questionHasCustomAnswer'] ?? [];

        $steps = [];

        foreach ($this->questions ?? [] as $key => $question) {
            $steps[] = Step::make($key)
                ->hiddenLabel()
                ->hidden(function($get) use ($question) {
                    if($question->dependent_question_id !== null){
                        $answers = $get("answers.{$question->dependent_question_id}");
                        return is_array($answers) ? !in_array($question->dependent_answer_id, $answers) : $answers != $question->dependent_answer_id;
                    }
                    return false;
                })
                ->extraAttributes([
                    'wire:key' => $question->id ,
                    'wire:ignore' => true,
                ])
                ->schema(fn($livewire) => $this->getQuestionComponent($question , $livewire));
        }

        return $steps;
    }

    protected function getQuestionComponent($question, $livewire): array
    {
        $binding = "answers.{$question->id}";

        $livewire->mountedActionsData[0]['answers'][$question->id] ??= null;

        $components = [
            match ($question->type) {
                QuestionType::SELECT => $this->getSelectComponent($question, $binding),
                QuestionType::Checkbox => $this->getCheckboxComponent($question, $binding, $livewire),
                QuestionType::Date => DatePicker::make($binding)
                    ->label($question->label)
                    ->suffixIcon('heroicon-m-calendar-days')
                    ->minDate(Carbon::now())
                    ->required($question->is_required)
                    ->stepComponentJsValidation()
                    ->validationMessages(['required' => __('string.please_provide_an_answer')]),
                QuestionType::Number => TextInput::make($binding)
                    ->label($question->label)
                    ->numeric()
                    ->stepComponentJsValidation()
                    ->required($question->is_required)
                    ->validationMessages(['required' => __('string.please_provide_an_answer')]),
                QuestionType::TextArea => Textarea::make($binding)
                    ->label($question->label)
                    ->required($question->is_required)
                    ->stepComponentJsValidation()
                    ->validationMessages(['required' => __('string.please_provide_an_answer')]),
                QuestionType::Attachments => $this->getAttachmentComponents($binding,$livewire, $question),
                QuestionType::Location => $this->getLocationComponent($binding, $livewire, $question),
                QuestionType::PreciseDate => $this->getPreciseDateComponent($binding,$livewire, $question),
                QuestionType::DateRange => $this->getDateRangeComponent($binding, $livewire, $question), // Add this line

                default => TextInput::make($binding)
                    ->label($question->label)
                    ->required($question->is_required)
                    ->stepComponentJsValidation()
                    ->validationMessages(['required' => __('string.please_provide_an_answer')]),
            }
        ];

        if($this->questionHasCustomAnswer[$question->id]) {
            foreach ($this->questionAnswers[$question->id] as $answer) {
                if ($answer->has_another_input) {

                    $customBinding = "customAnswers.{$question->id}";
                    $livewire->mountedActionsData[0]['customAnswers'][$question->id] ??= null;

                    $components[] =
                        $this->getCustomAnswerComponent($answer , $customBinding)
                            ->label(__('string.wizard.custom_answer'))
                            ->extraAlpineAttributes(fn($livewire, $component) => [
                                "x-on:step-{$question->id}-changed.window" => <<<JS
                                        const value = \$event.detail.value;
                                        const answers = \$wire.get('mountedActionsData.0.{$binding}') ?? null;
                                        const isAnswerIncluded =  Array.isArray(answers) ? answers.includes('{$answer->id}') : false;
                                        const isValueEqual = value == '{$answer->id}';
                                        const element = \$el.closest('[data-field-wrapper]');

                                        if ((isAnswerIncluded && !isValueEqual) || (isValueEqual && !isAnswerIncluded)) {
                                             element.classList.remove('hidden')
                                        }else{
                                             element.classList.add('hidden')
                                        }
                                JS,
                            ],true)
                            ->stepComponentJsValidation()
                            ->extraFieldWrapperAttributes([
                                'class' => 'hidden'
                            ])
                            ->required(fn(callable $get) => in_array($answer->id, (array)$get($binding)))
                            ->dehydratedWhenHidden()
                            ->dehydrateStateUsing(fn($state, $component) => $component->isVisible() ? $state : null)
                            ->validationMessages(['required' => __('string.please_provide_an_answer')]);
                }
            }
        }

        return $components;
    }


    protected function getSelectComponent(Question $question, $binding)
    {
        return Grid::make(1)
            ->schema([
                Radio::make($binding)
                    ->label($question->label)
                    ->options($this->questionAnswers[$question->id]?->pluck('label', 'id')?->toArray() ?? [])
                    ->live( condition:  $this->questionHasDependentQuestions[$question->id])
                    ->required($question->is_required)
                    ->extraInputAttributes(fn($component) => [
                        'onchange' => <<<JS
                            window.dispatchEvent(new CustomEvent("step-{$question->id}-changed",
                                { detail: { path: '{$component->getStatePath()}', value: this.value}}
                            ));
                        JS
                    ])
                    ->stepComponentJsValidation()
                    ->validationMessages(['required' => __('string.please_provide_an_answer')])
        ]);
    }

    protected function getCheckboxComponent(Question $question, $binding , $livewire)
    {
        $livewire->mountedActionsData[0]['answers'][$question->id] ??= [];

        return CheckboxList::make($binding)
            ->label($question->label)
            ->options($this->questionAnswers[$question->id]?->pluck('label', 'id')?->toArray() ?? [])
            ->live(condition:  $this->questionHasDependentQuestions[$question->id])
            ->extraInputAttributes(fn($component) =>[
                'onchange' => <<<JS
                    window.dispatchEvent(new CustomEvent("step-{$question->id}-changed",
                        { detail: { path: '{$component->getStatePath()}', value: this.value}}
                    ));
                JS
            ])
            ->required($question->is_required)
            ->stepComponentJsValidation()
            ->validationMessages(['required' => __('string.please_provide_an_answer')]);
    }

    protected function getAttachmentComponents($binding, $livewire, $question)
    {
        $livewire->mountedActionsData[0]['answers'][$question->id] ??= null;
        $livewire->mountedActionsData[0]['answers'][$question->id]['voice_note'] ??= null;
        $livewire->mountedActionsData[0]['answers'][$question->id]['attachments'] ??= [];

        return Grid::make(1)->schema([
            Placeholder::make('label')
                ->content($question->label)->hiddenLabel(),
            VoiceNote::make("$binding.voice_note")
                ->label(__('string.wizard.voice_note'))
                ->stepComponentJsValidation()

                ->required(fn($get) => $question->is_required && $get("$binding.voice_note") == null && count($get("$binding.attachments") ?? []) == 0),

            FileUpload::make("$binding.attachments")
                ->extraAlpineAttributes(fn($component) => [
                    'x-effect' => <<<JS
                        if(pond != null){
                            const handleFileProcessing = async () => {
                                if(pond == null){
                                    await init();
                                }
                                if (
                                    pond
                                        .getFiles()
                                        .filter(
                                            (file) =>
                                                file.status ===
                                                    window.FilePond.FileStatus.PROCESSING ||
                                                file.status ===
                                                    window.FilePond.FileStatus.PROCESSING_QUEUED,
                                        ).length
                                ) {
                                    return
                                }

                                dispatchFormEvent('form-processing-finished')
                            }
                            pond.off('processfile' )
                            pond.off('processfileabort')
                            pond.off('processfilerevert')
                            pond.on('processfile', handleFileProcessing)
                            pond.on('processfileabort', handleFileProcessing)
                            pond.on('processfilerevert', handleFileProcessing)
                        }
                JS
                ])
                ->label(__('string.wizard.attachments'))
                ->multiple()
                ->stepComponentJsValidation()
                ->required(fn($get) => $question->is_required && $get("$binding.voice_note") == null && count($get("$binding.attachments") ?? []) == 0),
        ]);
    }
    protected function getPreciseDateComponent($binding, $livewire, $question)
    {
        $livewire->mountedActionsData[0]['answers'][$question->id] ??= null;
        $livewire->mountedActionsData[0]['answers'][$question->id]['time'] ??= null;
        $livewire->mountedActionsData[0]['answers'][$question->id]['date'] ??= null;

        return Grid::make()->schema([
            Placeholder::make('label')
                ->content($question->label)->hiddenLabel(),
            Grid::make(2)->schema([
                DatePicker::make("{$binding}.date")
                    ->label(__('string.wizard.precise_date.date'))
                    ->required($question->is_required)
                    ->minDate(Carbon::now())
                    ->native()
                    ->stepComponentJsValidation()
                    ->validationMessages(['required' => __('string.please_provide_an_answer')]),

                TimePickerField::make("{$binding}.time")
                    ->label(__('string.wizard.precise_date.time'))
                    ->required($question->is_required)
                    ->stepComponentJsValidation()
                    ->validationMessages(['required' => __('string.please_provide_an_answer')]),
            ]),

            Grid::make(2)->schema([
                Select::make("{$binding}.duration_type")
                    ->label(__('string.wizard.precise_date.duration_type'))
                    ->options(__('string.wizard.duration_types'))
                    ->stepComponentJsValidation()
                    ->required($question->is_required)
                    ->validationMessages(['required' => __('string.please_provide_an_answer')]),

                TextInput::make("{$binding}.duration")
                    ->label(__('string.wizard.precise_date.duration'))
                    ->numeric()
                    ->required($question->is_required)
                    ->stepComponentJsValidation()
                    ->validationMessages(['required' => __('string.please_provide_an_answer')]),
            ])


        ]);
    }

    protected function getDateRangeComponent($binding, $livewire, $question)
    {
        $livewire->mountedActionsData[0]['answers'][$question->id] ??= null;

        return DateRangePicker::make($binding)
            ->disableClear(false)
            ->label($question->label)
            ->required($question->is_required)
            ->minDate(Carbon::now())
            ->ranges(fn($component) => [
                __('string.wizard.ranges.today') => [$component->now(), $component->now()],
                __('string.wizard.ranges.tomorrow') => [$component->now()->addDay(), $component->now()->addDay()],
                __('string.wizard.ranges.next_7_days') => [$component->now(), $component->now()->addDays(6)],
                __('string.wizard.ranges.next_30_days') => [$component->now(), $component->now()->addDays(29)],
                __('string.wizard.ranges.next_month') => [$component->now()->addMonth()->startOfMonth(), $component->now()->addMonth()->endOfMonth()],
                __('string.wizard.ranges.next_year') => [$component->now()->addYear()->startOfYear(), $component->now()->addYear()->endOfYear()],
            ])

            ->stepComponentJsValidation()
            ->validationMessages(['required' => __('string.please_provide_an_answer')]);

    }

    protected function getLocationComponent($binding , $livewire , $question)
    {
        $livewire->mountedActionsData[0]['answers'][$question->id]['location'] ??= [];
        $livewire->mountedActionsData[0]['answers'][$question->id]['location_name'] ??= [];

        return Grid::make(1)->schema([
            Placeholder::make('label')
                ->content($question->label)->hiddenLabel(),

            Map::make("$binding.location")
                ->label(__('string.wizard.map_location'))
                ->columnSpanFull()
                ->autocomplete(
                    fieldName: $binding.'.location_name',
                    placeField: 'name',
                    countries: [getCountryCode()]
                )
                ->defaultLocation(getTenant()->location)
                ->geolocateLabel('')
                ->draggable() // allow dragging to move marker
                ->clickable(true) // allow clicking to move marker
                ->geolocate() // adds a button to request device location and set map marker accordingly
                ->autocompleteReverse(true)
                ->defaultZoom(6)
                ->geolocateOnLoad(true, false)
                ->reactive()
                ->geolocate(),

            TextInput::make($binding.'.location_name')
                ->required($question->is_required)
                ->stepComponentJsValidation()
                ->rule(fn($get) => new LatLngInCountry($get('location.lat'), $get('location.lng')))
                ->label(__('string.wizard.descriptive_location'))
                ->prefix(__('localize.Choose').':')
                ->placeholder(__('services.requests.start_type')),

        ]);
    }

    protected function getCustomAnswerComponent($answer , $binding)
    {

        return match ($answer->type) {
            AnswerType::Date => DatePicker::make($binding)
                ->suffixIcon('heroicon-m-calendar-days'),
            AnswerType::Number => TextInput::make($binding)
                ->numeric(),
            AnswerType::TextArea => Textarea::make($binding),
            default => TextInput::make($binding),
        };
    }

}

