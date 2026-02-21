<?php

namespace App\Filament\Resources;

use App\Enums\AnswerType;
use App\Enums\QuestionType;
use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers\QuestionSuggestionsRelationManager;
use App\Forms\Components\TranslatableGrid;
use App\Models\Answer;
use App\Models\Question;
use App\Rules\SingleCustomAnswer;
use Awcodes\TableRepeater\Components\TableRepeater;
use Awcodes\TableRepeater\Header;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;

use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;
    protected static bool $shouldRegisterNavigation = false;
    protected static bool $isScopedToTenant = false;

    public static string $parentResource = ServiceResource::class;

    public static function getNavigationLabel(): string
    {
        return __('services.questions.plural');
    }

    public static function getModelLabel(): string
    {
        return __('services.questions.single');
    }

    public function getTitle(): string|Htmlable
    {
        return __('services.questions.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('services.questions.plural');
    }

    public static function getRecordTitle(?Model $record): string|null|Htmlable
    {
        return $record->label;
    }

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make()
                    ->schema([
                        TranslatableGrid::make()->textInput('label')->label(__('columns.question'))->required(),


                        Select::make('type')
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

                        TextInput::make('val')
                            ->visible(fn(Get $get) => !$get('is_custom') && in_array($get('type'), [
                                QuestionType::Location->value,
                                QuestionType::Text->value,
                                QuestionType::TextArea->value,
                                QuestionType::Attachments->value,
                                QuestionType::Number->value,
                                QuestionType::Date->value,
                                QuestionType::PreciseDate->value,
                                QuestionType::DateRange->value,
                            ]))
                            ->label(__('labels.Value'))
                            ->numeric()
                            ->dehydratedWhenHidden()
                            ->dehydrateStateUsing(fn($component, $state) => $component->isHidden() ? null : $state ?? 0)
                            ->default(0),

                        Checkbox::make('is_required')
                            ->default(true)
                            ->label(__('labels.is_required')),

                        Checkbox::make('is_custom')
                            ->label(__('labels.is_custom'))
                            ->reactive()
                            ->visible(fn(Get $get) => in_array($get('type'), [
                                QuestionType::Location->value,
                                QuestionType::Text->value,
                                QuestionType::TextArea->value,
                                QuestionType::Attachments->value,
                                QuestionType::Number->value,
                                QuestionType::Date->value,
                                QuestionType::PreciseDate->value,
                                QuestionType::DateRange->value,
                            ]))
                            ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                if ($state) {
                                    $set('answers', []);
                                }
                            }),

                        Forms\Components\Hidden::make('service_id')
                            ->default(fn() => request()->route('parent')),

                    ]),

                Forms\Components\Grid::make()
                    ->schema([
                        Select::make('dependent_question_id')
                            ->label(__('labels.choose_dependent_question'))
                            ->relationship('dependentQuestion', 'label',
                                function (Builder $query, callable $get, $operation, $record) {
                                    $parentId = $get('service_id');
                                    return $query->where(fn($query) => $query->where('type', QuestionType::SELECT)
                                        ->orWhere('type', QuestionType::Checkbox)
                                    )
                                        ->where('service_id', $parentId)
                                        ->when($record , fn($query) => $query->where('sort', '<', $record->sort)->where('id', $record->id) )
                                        ;
                                })

                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('dependent_answer_id', null);
                            }),
                        Select::make('dependent_answer_id')
                            ->label(__('labels.choose_dependent_answer'))
                            ->preload()
                            ->options(function (callable $get) {
                                $questionId = $get('dependent_question_id');
                                if (!$questionId) {
                                    return [];
                                }
                                return Answer::where('question_id', $questionId)->whereIsCustom(false)->pluck('label', 'id')->toArray();
                            })
                            ->searchable()
                            ->nullable(),
                    ]),

                Section::make()
                    ->compact()
                    ->schema([
                        TableRepeater::make('answers')
                            ->label(__('labels.Answers'))
                            ->headers([
                                Header::make('label_ar')->label(__('labels.ar_answer'))->width('200px'),
                                Header::make('label_en')->label(__('labels.en_answer'))->width('200px'),
                                Header::make('has_another_input')->label(__('labels.has_another_input'))->width('50px'),
                                Header::make('is_custom')->label(__('labels.is_custom'))->width('50px'),
                                Header::make('type')->label(__('columns.answer_type'))->width('120px'),
                                Header::make('val')->label(__('labels.Value'))->width('100px'),

                            ])
                            ->columnSpanFull()
                            ->relationship("answers")
                            ->orderColumn("sort")
                            ->minItems(1)
                            ->maxItems(fn(Get $get) => in_array($get('type'), [
                                QuestionType::Location->value,
                                QuestionType::Text->value,
                                QuestionType::TextArea->value,
                                QuestionType::Attachments->value,
                                QuestionType::Number->value,
                                QuestionType::Date->value,
                                QuestionType::PreciseDate->value,
                                QuestionType::DateRange->value,
                            ]) ? 1 : 99999)
                            ->schema([
                                TextInput::make('label.ar')->rtlDirection()->label(__('labels.ar_answer'))->required(),
                                TextInput::make('label.en')->ltrDirection()->label(__('labels.en_answer'))->required(),

                                Checkbox::make('has_another_input')
                                    ->label(__('labels.has_another_input'))
                                    ->reactive()
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        if (!$state) {
                                            $set('is_custom', false);
                                            $set('type', null);
                                            $set('val', 0);
                                        }
                                    }),

                                Checkbox::make('is_custom')
                                    ->label(__('labels.is_custom'))
                                    ->afterStateUpdated(function (callable $set, callable $get, $state) {
                                        if ($state) {
                                            $set('val', 0);
                                        }
                                    })
                                    ->hidden(fn($get) => $get('has_another_input') != 1)
                                    ->dehydratedWhenHidden()
                                    ->dehydrateStateUsing(fn($component, $state) => $component->isHidden() ? false : $state)
                                    ->reactive(),

                                Forms\Components\Placeholder::make('is_custom_placeholder')
                                    ->visible(fn($get) => $get('has_another_input') != 1)
                                    ->extraAttributes([
                                        'style' => 'display:none',
                                        'class' => 'table_repeater_placeholder'

                                    ])
                                    ->hiddenLabel(),


                                Select::make('type')
                                    ->options(AnswerType::class)
                                    ->required(fn(Get $get) => $get('has_another_input') == true)
                                    ->label(__('labels.answer_type'))
                                    ->placeholder(__('labels.select_type'))
                                    ->reactive()
                                    ->dehydratedWhenHidden()
                                    ->dehydrateStateUsing(fn($component, $state , $get) => $component->isHidden() ? null : ($get('has_another_input') ? AnswerType::getType($state) : null))
                                    ->visible(fn($get) => ($get('has_another_input'))),

                                Forms\Components\Placeholder::make('type_placeholder')
                                    ->hidden(fn($get) => ($get('has_another_input')))
                                    ->extraAttributes([
                                        'style' => 'display:none',
                                        'class' => 'table_repeater_placeholder'


                                    ])
                                    ->hiddenLabel(),

                                TextInput::make('val')
                                    ->hidden(fn(Get $get) => $get('is_custom') == 1)
                                    ->label(__('labels.Value'))
                                    ->numeric()
                                    ->dehydratedWhenHidden()
                                    ->dehydrateStateUsing(fn($component, $state) => $component->isHidden() ? null : $state)
                                    ->default(0),


                                Forms\Components\Placeholder::make('val_placeholder')
                                    ->extraAttributes([
                                        'style' => 'display:none',
                                        'class' => 'table_repeater_placeholder'
                                    ])
                                    ->visible(fn(Get $get) => $get('is_custom') == 1)
                                    ->hiddenLabel(),


                            ])
                            ->collapsible()
                            ->hint(__('labels.add_answers'))
                            ->rules([
                                new SingleCustomAnswer(),
                            ]),

                    ])
                    ->hidden(fn(Get $get) => $get('is_custom') || in_array($get("type"), [
                            QuestionType::Attachments->value,
                            QuestionType::Text->value,
                            QuestionType::TextArea->value,
                            QuestionType::Date->value,
                            QuestionType::PreciseDate->value,
                            QuestionType::DateRange->value,
                            QuestionType::Number->value,
                            QuestionType::Location->value])),

                Forms\Components\Hidden::make('sort')
                    ->dehydrateStateUsing(fn($livewire , $record) => $record != null ? $record->sort : ($livewire->parent->questions()->max('sort') +1)),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('label')
                    ->label(__('columns.label'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('type')
                    ->label(__('columns.type'))
                    ->searchable(),
                Tables\Columns\TextColumn::make('sort')
                    ->numeric()
                    ->sortable()
                    ->label(__('columns.sort')),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.created_date')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.updated_date')),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.deleted_date')),
            ])->reorderable('sort')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->url(
                        fn(Pages\ListQuestions $livewire, Model $record): string => static::$parentResource::getUrl('questions.edit', [
                            'record' => $record,
                            'parent' => $livewire->parent,
                        ])
                    ),
                Tables\Actions\ReplicateAction::make()->after(function (Question $replica, Question $record, ): void {
                    $record->answers->each(function ($answer) use ($replica) {
                        $answer->replicate()->fill(['question_id' => $replica->id])->save();
                    });
                }),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            QuestionSuggestionsRelationManager::class
        ];
    }


    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->orderBy('sort')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
