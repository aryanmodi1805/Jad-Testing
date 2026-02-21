<?php

namespace App\Filament\Resources\RequestResource\RelationManagers;

use App\Enums\QuestionType;
use App\Infolists\Components\AttachmentViewer;
use App\Infolists\Components\VoiceNoteEntry;
use App\Models\CustomerAnswer;
use Cheesegrits\FilamentGoogleMaps\Infolists\MapEntry;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\TextInputColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;

class CustomerAnswersRelationManager extends RelationManager
{
    protected static string $relationship = 'customerAnswers';
    public static function getNavigationLabel(): string
    {
        return __('services.customer_answers.plural');
    }

    public static function getModelLabel(): string
    {
        return __('services.customer_answers.plural');
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('services.customer_answers.plural');
    }

    public static function getPluralLabel(): ?string
    {
        return __('services.customer_answers.plural');
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('question.label')->label(__('columns.question'))
                    ->columnSpan(2)
                    ->wrap(),

                TextColumn::make('answer_type')
                    ->label(__('columns.answer_type'))
                    ->getStateUsing(fn ($record) => $record->question_type?->getLabel()),

                TextColumn::make('custom')->label(__('columns.the_answer'))
                    ->wrap()
                    ->html()
                    ->getStateUsing(fn($record) => customerTextAnswer($record)),

                TextInputColumn::make('val')
                    ->type('number')
                    ->width('200')
                    ->label(__('columns.value')),
            ])
            ->filters([
                //
            ])
            ->headerActions([
            ])
            ->actions([
                Action::make('viewAttachments')
                    ->label(__('labels.view_attachments'))
                    ->visible(fn($record) => $record->question_type == QuestionType::Attachments)
                    ->infolist([
                        RepeatableEntry::make('attachment')
                            ->label(__('columns.attachments'))
                            ->schema([
                                TextEntry::make('path')
                                    ->hiddenLabel()
                                    ->url(fn($state) => Storage::disk('public')->url($state),true)
                            ]),

                        VoiceNoteEntry::make('voice_note')
                            ->label(__('columns.voice_note')),
                    ])->modalSubmitAction(false),

                Action::make('viewLocation')
                    ->color('warning')
                    ->label(__('labels.view_location'))
                    ->visible(fn($record) => $record->question_type == QuestionType::Location)
                    ->infolist([
                        TextEntry::make('text_answer')->label(__('columns.location')),
                        MapEntry::make('location')->label(__('columns.map'))
                            ->columnSpan(2),
                    ])->modalSubmitAction(false),

                Action::make('viewPreciseDate')
                    ->color('info')
                    ->label(__('labels.view_precise_date'))
                    ->visible(fn($record) => $record->question_type == QuestionType::PreciseDate && !is_null($record->time) && !is_null($record->duration))
                    ->infolist([
                        TextEntry::make('text_answer')->label(__('labels.date')),
                        TextEntry::make('time')->label(__('columns.time')),
                        TextEntry::make('duration')->label(__('columns.duration')),
                        TextEntry::make('duration_type')->label(__('columns.duration_type')),
                    ])->modalSubmitAction(false),

                DeleteAction::make()
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
