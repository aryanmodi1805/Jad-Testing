<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers\CountriesRelationManager;
use App\Forms\Components\Translatable as ComponentsTranslatable;
use App\Models\User;
use Archilex\AdvancedTables\Models\UserView;
use Filament\Forms\Components\BelongsToManyMultiSelect;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MultiSelect;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $slug = 'users';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = -3;

    public static function getNavigationGroup(): ?string
    {
        return __('nav.accounts');
    }

    public static function getNavigationLabel(): string
    {
        return __('accounts.users.plural');
    }

    public static function getModelLabel(): string
    {
        return __('accounts.users.plural');
    }

    public function getTitle(): string|Htmlable
    {
        return __('accounts.users.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('accounts.users.plural');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->required()
                    ->label(__('columns.name')),

                TextInput::make('email')
                    ->required()
                    ->label(__('columns.email')),

                Select::make('countries')
                    ->multiple()
                    ->relationship('countries', 'name', function (Builder $query) {
                        return $query->where('active', 1);
                    })
                    ->getOptionLabelFromRecordUsing(fn(Model $record) => "{$record->full_name}")
                    ->saveRelationshipsUsing(function ($record, $state) {
                        $record->countries()->sync($state);
                    })
                    ->preload()
                    ->label(__('columns.countries')),

                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->label(__('string.role'))
                    ->multiple()
                    ->preload()
                    ->searchable(),


                TextInput::make('password')
                    ->label(__('filament-breezy::default.fields.new_password'))
                    ->same('password_confirmation')
                    ->password()
                    ->extraInputAttributes([
                        "autocomplete" => "new-password"
                    ])
                    ->required(fn($record) => $record === null)
                    ->dehydrated(fn($state) => !empty($state))
                    ->dehydrateStateUsing(fn($state) => !empty($state) ? Hash::make($state) : ''),
                TextInput::make('password_confirmation')
                    ->password()
                    ->dehydrated(false)
                    ->extraInputAttributes([
                        "autocomplete" => "new-password"
                    ])
                    ->maxLength(255)
                    ->label(__('filament-breezy::default.fields.new_password_confirmation')),


                Toggle::make('email_verified_at')
                    ->label(__('columns.email_verified'))
                    ->formatStateUsing(fn($record) => $record?->email_verified_at !=null)
                    ->dehydrateStateUsing(function ($state, $record) {
                        if ($state && $record?->email_verified_at == null) {
                            return now();
                        } elseif ($state && $record?->email_verified_at != null) {
                            return $record->email_verified_at;
                        } else {
                            return null;
                        }
                    }),

            ]);

    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('columns.name')),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label(__('columns.email')),
            ])
            ->filters([
                //
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make()
                ->action(function ($record) {
                    $record->managedUserViews()->detach();
                    $record->managedPresetViews()->delete();
                    UserView::where('user_id', $record->id)->delete();
                    $record->countries()->detach();
                    $record->delete();
                }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                    ->action(function ($records) {
                        foreach ($records as $record) {
                            $record->managedUserViews()->detach();
                            $record->managedPresetViews()->delete();
                            UserView::where('user_id', $record->id)->delete();
                            $record->countries()->detach();
                            $record->delete();
                        }
                    }),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            CountriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

//    public static function getGloballySearchableAttributes(): array
//    {
//        return ['name', 'email'];
//    }
}
