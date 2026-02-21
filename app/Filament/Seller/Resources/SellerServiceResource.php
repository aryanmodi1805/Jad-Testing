<?php

namespace App\Filament\Seller\Resources;

use App\Filament\Seller\Resources\QuestionResource\Pages\CreateQuestion;
use App\Filament\Seller\Resources\QuestionResource\Pages\EditQuestion;
use App\Filament\Seller\Resources\QuestionResource\Pages\ListQuestions;
use App\Filament\Seller\Resources\SellerServiceResource\Pages;
use App\Filament\Seller\Resources\SellerServiceResource\RelationManagers\LocationsRelationManager;
use App\Models\QuestionSuggestion;
use App\Models\SellerService;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;

class SellerServiceResource extends Resource
{
    protected static ?string $model = SellerService::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $shouldSkipAuthorization = true;
    protected static ?int $navigationSort = 4;


    public static function getModelLabel(): string
    {
        return __('seller.services.single');
    }

    public function getTitle(): string|Htmlable
    {
        return __('seller.services.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('seller.services.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('service_id')
                    ->columnSpanFull()
                    ->relationship('service', 'name', function ($query, $record) {
                        return ($query->where(fn($query)=>
                        $query->notAssignedToSeller(auth('seller')->id())->orWhere('id', $record?->service_id)

                        )->where('country_id',getCountryId()));
                    })
                    ->searchable()
                    ->preload()
                    ->required()
                    ->label(__('columns.service')),
                Hidden::make('seller_id')
                    ->default(auth('seller')->id()),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('service.name')
                    ->searchable()
                    ->label(__('columns.service_name')),
                TextColumn::make('locations')
                    ->label(__('columns.locations'))
                    ->formatStateUsing(fn($state, $record) => $record->locations->count())
                    ->sortable(false)
                    ->searchable(false),
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
            ])
            ->filters([
                //
            ])
            ->actions([
                Action::make('viewQuestions')
                    ->color('success')
                    ->icon('heroicon-o-question-mark-circle')
                    ->url(
                        fn (SellerService $record): string => static::getUrl('questions.index', [
                            'parent' => $record->service_id,
                        ])
                    )
                    ->label(__('labels.view_questions')),
                Action::make('suggestQuestion')
                    ->label(__('labels.suggest_new_question'))
                    ->modalHeading(__('labels.suggest_new_question'))
                    ->form(fn ($record) => self::getSuggestQuestionForm($record))
                    ->action(function (array $data, SellerService $record) {
                        $questionSuggestion = QuestionSuggestion::create([
                            'service_id' => $record->service_id,
                            'type' => 'create',
                            'name' => $data['name'],
                            'question_type' => $data['type'],
                            'seller_id' => auth('seller')->id()
                        ]);

                        if (!empty($data['answers'])) {
                            foreach ($data['answers'] as $answerData) {
                                $questionSuggestion->answerSuggestions()->create([
                                    'value' => $answerData['value'],
                                ]);
                            }
                        }
                    }),
                DeleteAction::make(),
                Tables\Actions\EditAction::make()
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                ]),
            ]);
    }

    private static function getSuggestQuestionForm($record): array
    {
        return [
            Select::make('type')
                ->options([
                    'select' => __('services.questions.types.select'),
                    'checkbox' => __('services.questions.types.checkbox'),
                    'text' => __('services.questions.types.text'),
                    'textarea' => __('services.questions.types.textarea'),
                    'date' => __('services.questions.types.date'),
                ])
                ->live()
                ->required()
                ->label(__('columns.question_type')),
            TextInput::make('name')
                ->label(__('columns.question_name'))
                ->required(),
            Repeater::make('answers')
                ->label(__('columns.suggest_answers'))
                ->schema([
                    TextInput::make('value')
                        ->label(__('columns.answer'))
                        ->required(),
                ])
                ->visible(fn($get) => in_array($get('type'), ['select', 'checkbox'])),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageSellerServices::route('/'),


            'questions.index' => ListQuestions::route('/{parent}/questions'),
            'questions.create' => CreateQuestion::route('/{parent}/questions/create'),
            'questions.edit' => EditQuestion::route('/{parent}/questions/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('seller', function (Builder $query) {
                $query->where('id', auth('seller')->id());
            });
    }
}
