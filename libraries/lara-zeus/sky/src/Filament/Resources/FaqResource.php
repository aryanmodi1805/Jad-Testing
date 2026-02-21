<?php

namespace LaraZeus\Sky\Filament\Resources;

use App\Enums\FAQLocationType;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieTagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Form;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\SpatieTagsColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use LaraZeus\Sky\Filament\Resources\FaqResource\Pages;
use LaraZeus\Sky\SkyPlugin;

class FaqResource extends SkyResource
{
    protected static ?string $navigationIcon = 'heroicon-o-folder-open';

    protected static ?int $navigationSort = 3;

    public static function getModel(): string
    {
        return SkyPlugin::get()->getModel('Faq');
    }

    public static function getLabel(): string
    {
        return __('string.faq.single');
    }

    public static function getPluralLabel(): string
    {
        return __('string.faq.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('string.faq.title');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Textarea::make('question')
                    ->label(__('string.faq.question'))
                    ->required()
                    ->maxLength(65535)
                    ->columnSpan(2),

                RichEditor::make('answer')
                    ->label(__('string.faq.answer'))

                    ->required()
                    ->maxLength(65535)
                    ->columnSpan(2),

                Select::make('location')
                    ->options(__('string.faq.location_types'))
//                    ->dehydrateStateUsing(function () {
//                        return FAQLocationType::Seller->value;
//                    })
//                    ->dehydratedWhenHidden()
                    ->required()
                    ->label(__('string.faq.location_type')),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('question')->searchable()->label(__('string.faq.question')),

                TextColumn::make('location')
                    ->label(__('string.type'))
            ])
            ->filters([
                SelectFilter::make('tags')
                    ->multiple()
                    ->relationship('tags', 'name')
                    ->label(__('Tags')),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make('edit')->label(__('Edit')),
                    DeleteAction::make('delete')
                        ->label(__('Delete')),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit' => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
