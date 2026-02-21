<?php

namespace App\Filament\Resources;

use App\Enums\RateableType;
use App\Filament\Resources\QuestionResource\Pages\CreateQuestion;
use App\Filament\Resources\QuestionResource\Pages\EditQuestion;
use App\Filament\Resources\QuestionResource\Pages\ListQuestions;
use App\Filament\Resources\QuestionSuggestionResource\Pages\ListQuestionSuggestion;
use App\Filament\Resources\ServiceResource\Actions\getBarkQuestionsAction;
use App\Filament\Resources\ServiceResource\Pages;
use App\Forms\Components\Translatable;
use App\Forms\Components\TranslatableGrid;
use App\Models\Answer;
use App\Models\Question;
use App\Models\Rating;
use App\Models\Scopes\ActiveScope;
use App\Models\Scopes\ServiceActiveScope;
use App\Models\Scopes\ServiceHasQuestionsScope;
use App\Models\Service;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ServiceResource extends Resource
{
    protected static ?string $model = Service::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $isScopedToTenant = true;
    protected static ?int $navigationSort = -10;

    public static function getNavigationGroup(): ?string
    {
        return __('nav.services');
    }

    public static function getNavigationLabel(): string
    {
        return __('services.services.plural');
    }

    public static function getModelLabel(): string
    {
        return __('services.services.single');
    }

    public function getTitle(): string|Htmlable
    {
        return __('services.services.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('services.services.plural');
    }

    public static function getRecordTitle(?Model $record): string|null|Htmlable
    {
        return $record->name;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->columns(1)
            ->schema([
                TranslatableGrid::make()->textInput('name')->label(__('string.name'))->required()->live(onBlur: true)
                    ->afterStateUpdated(function ($set, $state) {
                        $set('slug', Str::slug($state['en']));
                    }),


                Forms\Components\Select::make('category_id')
                    ->relationship('category', 'name', fn($query) => $query->whereNotNull('parent_id'))
                    ->preload()
                    ->required()
                    ->searchable()
                    ->label(__('columns.subcategories_name')),
                Forms\Components\TextInput::make('slug')
                    ->unique(ignorable: fn(?Model $record): ?Model => $record)
                    ->required()
                    ->maxLength(255)
                    ->label(__('columns.slug')),
                Forms\Components\TagsInput::make('keywords')
                    ->dehydrateStateUsing(fn($state) => $state ?? [])
                    ->formatStateUsing(fn($record) => $record?->keywords ?? [])
                    ->label(__('string.keywords')),

                Forms\Components\Toggle::make('is_nationwide')
                    ->required()
                    ->label(__('columns.is_nationwide')),
                Forms\Components\Toggle::make('is_remote')
                    ->required()
                    ->label(__('columns.is_remote')),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->previewable()
                    ->label(__('string.image'))
                    ->imageEditor()
                    ->imageResizeMode('cover')
                    ->directory('service_images')
                ,
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->toggleable()
                    ->label(__('columns.name')),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->toggleable()
                    ->label(__('columns.slug')),
                Tables\Columns\IconColumn::make('is_nationwide')
                    ->boolean()
                    ->toggleable()
                    ->width(50)
                    ->label(__('columns.is_nationwide')),
                Tables\Columns\IconColumn::make('is_remote')
                    ->boolean()
                    ->toggleable()
                    ->label(__('columns.is_remote')),
                Tables\Columns\TextColumn::make('category.name')
                    ->searchable()
                    ->label(__('columns.categories_name')),
                Tables\Columns\TextColumn::make('subcategory.name')
                    ->searchable()
                    ->label(__('columns.subcategories_name')),
                Tables\Columns\TextColumn::make('questions_count')
                    ->counts("questions")
                    ->toggleable()
                    ->sortable()
                    ->label(__('columns.questions_count')),

                Tables\Columns\ToggleColumn::make('active')->label(__('columns.active')),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.created_at')),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.updated_at')),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->label(__('columns.deleted_at')),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('category')
                    ->label(__('services.categories.name'))
                    ->preload()
                    ->relationship('category', 'name')
                    ->multiple(),
                Tables\Filters\TernaryFilter::make('active')
                    ->label(__('columns.active'))
                    ->default(true)
                    ->options([
                        true => __('columns.active'),
                        false => __('columns.inactive'),
                    ]),
            ])
            ->actions([
                Action::make('toggleShowOnHomepage')
                    ->label(fn($record) => $record->show_on_home_page ? __('columns.hide_from_homepage') : __('columns.show_on_homepage'))
                    ->action(function (Service $record) {
                        $record->show_on_home_page = !$record->show_on_home_page;
                        $record->save();
                    })
                    ->icon(fn($record) => $record->show_on_home_page ? 'heroicon-o-eye' : 'heroicon-o-eye-slash')
                    ->color(fn($record) => $record->show_on_home_page ? 'success' : 'danger')
                ,
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('Manage Questions')
                    ->label(__('string.manage_questions'))
                    ->color('success')
                    ->icon('heroicon-m-academic-cap')
                    ->url(
                        fn($record): string => static::getUrl('questions.index', [
                            'parent' => $record->id,
                        ])
                    ),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ReplicateAction::make()
                    ->beforeReplicaSaved(function (Service $replica): void {
                        unset($replica->questions_count);
                    })
                    ->after(function (Service $replica, Service $record): void {
                        $record->questions->each(function (Question $question) use ($replica) {
                            $newQuestion = $question->replicate(['questions_count']);
                            $newQuestion->service_id = $replica->id;
                            $newQuestion->save();

                            $question->answers->each(function (Answer $answer) use ($newQuestion) {
                                $newAnswer = $answer->replicate();
                                $newAnswer->question_id = $newQuestion->id;
                                $newAnswer->save();
                            });
                        });
                    }),
                getBarkQuestionsAction::make()->requiresConfirmation()->visible(fn($record) => !empty($record->bark_id)),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListServices::route('/'),
            'create' => Pages\CreateService::route('/create'),
            'edit' => Pages\EditService::route('/{record}/edit'),

            // Questions
            'questions.index' => ListQuestions::route('/{parent}/questions'),
            'questions.create' => CreateQuestion::route('/{parent}/questions/create'),
            'questions.edit' => EditQuestion::route('/{parent}/questions/{record}/edit'),

            //Question-Suggestions
            'question-suggestions.index' => ListQuestionSuggestion::route('/{parent}/question-suggestions'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
                ServiceActiveScope::class,
                ServiceHasQuestionsScope::class
            ]);
    }
}
