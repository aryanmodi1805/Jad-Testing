<?php

namespace App\Filament\Resources;

use App\Filament\Actions\BlockAction;
use App\Filament\Resources\CustomerResource\Pages;
use App\Models\Customer;
use App\Models\Seller;
use Filament\Actions\Action;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\ColumnGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;
use Mokhosh\FilamentRating\Columns\RatingColumn;
use Illuminate\Database\Eloquent\Builder;


class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static ?string $slug = 'customers';
    protected static ?int $navigationSort = -5;
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $isScopedToTenant = true;

    public static function getNavigationGroup(): ?string
    {
        return __('nav.accounts');
    }

    public static function getNavigationLabel(): string
    {
        return __('accounts.customers.plural');
    }

    public static function getModelLabel(): string
    {
        return __('accounts.customers.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('accounts.customers.plural');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make()
                    ->schema([
                        Grid::make()
                            ->schema([
                                TextEntry::make('name')
                                    ->label(__('columns.name'))
                                    ->icon('heroicon-o-user')
                                    ->iconPosition('before'),

                                TextEntry::make('email')
                                    ->label(__('columns.email'))
                                    ->icon('heroicon-o-envelope-open')
                                    ->iconPosition('before'),

                                TextEntry::make('phone')
                                    ->label(__('columns.phone'))
                                    ->icon('heroicon-o-phone')
                                    ->iconPosition('before'),


                            ])
                            ->columns([

                            ]),
                    ])
                    ->columns(),
            ]);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Grid::make()
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->label(__('columns.name')),
                        TextInput::make('phone')
                            ->unique(table: 'customers',column: 'phone',ignoreRecord: true)
                            ->required()
                            ->label(__('columns.phone')),

                        TextInput::make('email')
                            ->unique(table: 'customers',column: 'email',ignoreRecord: true)

                            ->required()
                            ->label(__('columns.email'))
                            ->columnSpan(2),


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
                        Toggle::make('phone_verified_at')
                            ->label(__('columns.phone_verified'))
                            ->formatStateUsing(fn($record) => $record?->phone_verified_at != null)
                            ->dehydrateStateUsing(function ($state, $record) {
                                if ($state && $record?->phone_verified_at == null) {
                                    return now();
                                } elseif ($state && $record?->phone_verified_at != null) {
                                    return $record->phone_verified_at;
                                } else {
                                    return null;
                                }
                            }),
                        Toggle::make('email_verified_at')
                            ->label(__('columns.email_verified'))
                            ->formatStateUsing(fn($record) => $record?->email_verified_at != null)
                            ->dehydrateStateUsing(function ($state, $record) {
                                if ($state && $record?->email_verified_at == null) {
                                    return now();
                                } elseif ($state && $record?->email_verified_at != null) {
                                    return $record->email_verified_at;
                                } else {
                                    return null;
                                }
                            }),

                        Actions::make([
                            Actions\Action::make('associate_account')
                                ->label(__('string.associate_with_seller'))
                                ->button()
                                ->requiresConfirmation()
                                ->modalDescription(__('string.associate_with_seller_description'))
                                ->icon('heroicon-o-link')
                                ->color('secondary')

                                ->form([
                                    Select::make('seller_id')
                                        ->label(__('columns.seller'))
                                        ->options(Seller::whereNull('customer_id')->pluck('name','id'))
                                    ->preload()
                                        ->required()
                                    ->searchable()
                                    ->optionsLimit(20)
                                ])
                                ->action(function ($record,$data){
                                    $record->update([
                                        'seller_id' => $data['seller_id']
                                    ]);

                                    $record->associatedAccount->update([
                                        'customer_id' => $record->id
                                    ]);
                                })
                                ->visible(fn($record) => $record->seller_id == null),
                        Actions\Action::make('de_associate_account')
                                ->label(__('string.de_associate_with_seller'))
                                ->button()

                                ->requiresConfirmation()
                                ->modalDescription(__('string.de_associate_with_seller_description'))
                                ->icon('tabler-unlink')
                                ->color('danger')


                                ->action(function ($record){

                                    $record->associatedAccount->update([
                                        'customer_id' => null
                                    ]);

                                    $record->update([
                                        'seller_id' => null
                                    ]);
                                })
                                ->visible(fn($record) => $record->seller_id != null)
                        ])->visible(fn($operation) => $operation == "edit"),


                    ]),


            ])->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns(components: [
                TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->label(__('columns.name')),
                TextColumn::make('phone')
                    ->searchable()
                    ->label(__('columns.phone'))->extraCellAttributes(['style'=>'direction:ltr !important']),

                TextColumn::make('email')
                    ->searchable()
                    ->sortable()
                    ->label(__('columns.email')),

                RatingColumn::make('rate')
                    ->label(__('seller.rate.single'))->size('sm'),
                IconColumn::make('blocked')
                    ->label(__('columns.block'))
                    ->boolean(),
                TextColumn::make('created_at')->label(__('columns.created_date'))->size('xs')
                    ->dateTime('d-m-Y H:i a')
                    ->sortable(),
                ColumnGroup::make(__('columns.verified'),
                    [
                        IconColumn::make('email_verified_at')
                            ->grow(false)
                            ->wrapHeader(true)
                            ->label(__('columns.email'))
                            ->boolean(),
                        IconColumn::make('phone_verified_at')
                            ->grow(false)
                            ->wrapHeader(true)
                            ->label(__('columns.phone'))
                            ->boolean(),
                    ]
                ),


            ])
            ->filters([
                TrashedFilter::make(),
                Filter::make('email_verified_at')
                    ->label(__('columns.email_verified'))
                    ->toggle()
                    ->modifyQueryUsing(fn($query) => $query->whereNotNull('email_verified_at')),
                Filter::make('phone_verified_at')
                    ->default()
                    ->label(__('columns.phone_verified'))
                    ->modifyQueryUsing(fn($query) => $query->whereNotNull('phone_verified_at'))
                    ->toggle(),

                Filter::make('blocked')
                    ->label(__('columns.block'))
                    ->modifyQueryUsing(fn($query) => $query->where('blocked', true))
                    ->toggle(),

            ])
            ->actions([
//                ratingTableAction::make()->label(__('columns.rate')),
                ViewAction::make(),
                BlockAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class])->orderBy('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomers::route('/'),

        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }

    public function getTitle(): string|Htmlable
    {
        return __('accounts.customers.single');
    }

}
