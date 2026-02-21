<?php

namespace App\Filament\Seller\Resources;

use App\Enums\QuestionType;
use App\Models\Question;
use App\Models\QuestionSuggestion;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldRegisterNavigation = false;
    protected static bool $shouldSkipAuthorization = true;

    public static string $parentResource = SellerServiceResource::class;
    public static ?string $parentModel = Service::class;

    public static function getRecordTitle(Model|null $record): string|null|\Illuminate\Contracts\Support\Htmlable
    {
        return $record?->full_name;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label(__('columns.name'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->searchable()
                    ->label(__('columns.type')),
            ])
            ->actions([
                Action::make('suggestEdit')
                    ->label(__('labels.suggest_edit'))
                    ->modalHeading(__('labels.suggest_edit'))
                    ->form(self::getSuggestEditForm())
                    ->fillForm(fn($record) => $record->toArray())
                    ->action(function (array $data, Question $record) {

                        $answersData = $data['answers'] ?? [];

                        $questionSuggestion = QuestionSuggestion::create([
                            'service_id' => $record->service_id,
                            'question_id' => $record->id,
                            'type' => 'edit',
                            'name' =>  $data['label'],
                            'question_type' =>$data['type'],
                            'seller_id' => auth('seller')->id()
                        ]);

                        $existingAnswers = $record->answers->pluck('label')->toArray();

                        foreach ($answersData as $answerData) {
                            if (!in_array($answerData['value'], $existingAnswers)) {
                                $questionSuggestion->answerSuggestions()->create([
                                    'question_suggestion_id' => $questionSuggestion->id,
                                    'value' => $answerData['value'],
                                ]);
                            } else {
                                $existingAnswer = $record->answers->firstWhere('label', $answerData['value']);
                                if ($existingAnswer && $existingAnswer->label !== $answerData['value']) {
                                    $questionSuggestion->answerSuggestions()->create([
                                        'question_suggestion_id' => $questionSuggestion->id,
                                        'value' => $answerData['value'],
                                    ]);
                                }
                            }
                        }

                        Log::info('Edit suggestion made for question ID: ' . $record->id, ['data' => $data]);
                    }),
                Action::make('suggestDelete')
                    ->label(__('labels.suggest_delete'))
                    ->requiresConfirmation()
                    ->modalHeading(__('labels.suggest_delete'))
                    ->action(function (Question $record) {
                        QuestionSuggestion::create([
                            'service_id' => $record->service_id,
                            'question_id' => $record->id,
                            'type' => 'delete',
                            'seller_id' => auth('seller')->id()
                        ]);

                        Log::info('Delete suggestion made for question ID: ' . $record->id);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([]),
            ]);
    }

    private static function getSuggestEditForm(): array
    {
        return [
            Forms\Components\TextInput::make('label')
                ->formatStateUsing(fn($record) => $record->label)
                ->label(__('columns.question_name'))
//                ->default(fn($record) => $record->label)
                ->required(),

            Forms\Components\Select::make('type')
                ->formatStateUsing(fn($record) => $record->type->value)
                ->options(QuestionType::class)
                ->required()
                ->default(QuestionType::SELECT->value)
                ->label(__('labels.question_type'))
                ->placeholder(__('labels.select_type'))
                ->live()
                ->afterStateUpdated(function ($state, callable $set, callable $get) {
                    if (in_array($state, [
                        QuestionType::Location->value,
                        QuestionType::Text->value,
                        QuestionType::TextArea->value,
                        QuestionType::Attachments->value,
                        QuestionType::Number->value,
                        QuestionType::Date->value,
                        QuestionType::PreciseDate->value,
                        QuestionType::DateRange->value,
                    ])) {
                        $set('answers', [
                            [
                                'label' => [
                                    'ar' => null,
                                    'en' => null,
                                ],
                                'has_another_input' => false,
                                'is_custom' => false,
                                'type' => null,
                                'val' => 0,
                            ],
                        ]);
                        $set('is_custom',false);
                        $set('val',$get('val') ?? 0);
                    }
                }),

            Forms\Components\Repeater::make('answers')
                            ->relationship()
                            ->label(fn($record) => __('columns.suggest_answers_optional'))
                            ->simple(
                                Forms\Components\TextInput::make('label')
                                    ->formatStateUsing(fn($record) => $record->label)
                                    ->label(__('columns.answer'))
                                    ->required(),

                           )
//                ->default($record->answers->map(function ($answer) {
//                    return [
//                        'value' => $answer->label,
//                    ];
//                })->toArray())
                ->addActionLabel(__('labels.add_answer'))
                ->collapsed(false),
        ];
    }


    public static function getRelations(): array
    {
        return [
            //
        ];
    }
}
