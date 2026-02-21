<?php

namespace App\Filament\Resources;

use App\Filament\Actions\BlockAction;
use App\Filament\Resources\SellerResource\Pages;
use App\Filament\Resources\SellerResource\RelationManagers\QasRelationManager;
use App\Filament\Resources\SellerResource\RelationManagers\QuestionSuggestionsRelationManager;
use App\Filament\Resources\SellerResource\RelationManagers\RequestsRelationManager;
use App\Filament\Resources\SellerResource\RelationManagers\ResponsesRelationManager;
use App\Filament\Resources\SellerResource\RelationManagers\SellerLocationsRelationManager;
use App\Filament\Resources\SellerResource\RelationManagers\SellerProfileServicesRelationManager;
use App\Filament\Resources\SellerResource\RelationManagers\SellerQAsRelationManager;
use App\Filament\Resources\SellerResource\RelationManagers\SellerServicesRelationManager;
use App\Filament\Resources\SellerResource\RelationManagers\ServicesRelationManager;
use App\Filament\Resources\SellerResource\RelationManagers\SocialMediaRelationManager;
use App\Forms\Components\TranslatableGrid;
use App\Models\Customer;
use App\Models\Scopes\ActiveScope;
use App\Models\Seller;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\ImageEntry;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Hash;

class SellerResource extends Resource
{
    protected static ?string $model = Seller::class;

    protected static ?string $slug = 'sellers';

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = -4;
    protected static bool $isScopedToTenant = true;
//    protected static ?string $tenantRelationshipName = 'country';

    public static function getNavigationGroup(): ?string
    {
        return __('nav.accounts');
    }

    public static function getNavigationLabel(): string
    {
        return __('accounts.sellers.plural');
    }

    public static function getModelLabel(): string
    {
        return __('accounts.sellers.single');
    }

    public function getTitle(): string|Htmlable
    {
        return __('accounts.sellers.single');
    }

    public static function getPluralLabel(): ?string
    {
        return __('accounts.sellers.plural');
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

                                TextEntry::make('country.name')
                                    ->label(__('columns.country'))
                                    ->icon('heroicon-o-globe-europe-africa')
                                    ->iconPosition('before'),

                                TextEntry::make('company_description')
                                    ->label(__('seller.company.company_description'))
                                    ->icon('heroicon-o-document-text')
                                    ->iconPosition('before'),

                                TextEntry::make('years_in_business')
                                    ->label(__('seller.company.years_in_business'))
                                    ->icon('heroicon-o-calendar')
                                    ->iconPosition('before'),

                                TextEntry::make('companySize.name')
                                    ->label(__('seller.company.company_size_id'))
                                    ->icon('heroicon-o-users')
                                    ->iconPosition('before'),

                                TextEntry::make('website')
                                    ->label(__('seller.company.website'))
                                    ->icon('heroicon-o-globe-alt')
                                    ->url(fn($record) => $record->website)
                                    ->openUrlInNewTab()
                                    ->iconPosition('before'),

                                TextEntry::make('company_name')
                                    ->label(__('seller.company.company_name'))
                                    ->icon('heroicon-o-building-office')
                                    ->iconPosition('before'),

                                ImageEntry::make('avatar_url')
                                    ->label(__('filament-breezy::default.fields.avatar'))

                            ])
                            ->columns([]),
                    ])
                    ->columns(),
            ]);

    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                \Filament\Forms\Components\Grid::make()
                    ->columns(2)
                    ->schema([

                        TextInput::make('name')
                            ->label(__('columns.name'))
                            ->required(),
                        TextInput::make('phone')
                            ->unique(table: 'sellers',column: 'phone',ignoreRecord: true)
                            ->label(__('columns.phone')),

                        TextInput::make('email')
                            ->unique(table: 'sellers',column: 'email',ignoreRecord: true)
                            ->label(__('columns.email'))
                            ->columnSpan(2)
                            ->required(),

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




//                        Select::make('country_id')
//                            ->label(__('columns.country'))
//                            ->relationship('country', 'name')
//                            ->searchable(),



                           TextInput::make('years_in_business')
                            ->label(__('seller.company.years_in_business'))
                            ->numeric(),

                        Select::make('company_size_id')
                            ->label(__('seller.company.company_size_id'))
                            ->relationship('companySize', 'name'),

                        TextInput::make('website')
                            ->label(__('seller.company.website')),

                        TranslatableGrid::make()->textInput('company_name')
                            ->columns(2)
                            ->label(__('seller.company.company_name'))
                          ,

                        TranslatableGrid::make()->textArea('company_description')
                            ->columns(2)
                            ->label(__('seller.company.company_description')),

                        FileUpload::make('avatar_url')
                            ->label(__('filament-breezy::default.fields.avatar')),

                        FileUpload::make('identification_document_url')
                            ->label(__('seller.identification_document'))
                            ->disk('public')
                            ->directory('identification_documents')
                            ->acceptedFileTypes(['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'])
                            ->maxSize(10240) // 10MB
                            ->downloadable()
                            ->openable()
                            ->previewable(),

                        Select::make('identification_document_status')
                            ->label(__('seller.identification_status'))
                            ->options([
                                'pending' => __('seller.status.pending'),
                                'approved' => __('seller.status.approved'),
                                'rejected' => __('seller.status.rejected'),
                            ])
                            ->default('pending')
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state === 'approved') {
                                    $set('identification_document_verified_at', now());
                                } elseif ($state === 'rejected') {
                                    $set('identification_document_verified_at', null);
                                }
                            }),

                        Textarea::make('identification_document_rejection_reason')
                            ->label(__('seller.rejection_reason'))
                            ->visible(fn (callable $get) => $get('identification_document_status') === 'rejected')
                            ->required(fn (callable $get) => $get('identification_document_status') === 'rejected'),


                        Group::make()
                        ->schema([
                            Toggle::make('blocked')
                                ->label(__('columns.block')),

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
                                    if ($state && $record->email_verified_at == null) {
                                        return now();
                                    } elseif ($state && $record?->email_verified_at != null) {
                                        return $record->email_verified_at;
                                    } else {
                                        return null;
                                    }
                                }),
                        ]),

                        Actions::make([
                            Actions\Action::make('associate_account')
                                ->label(__('string.associate_with_customer'))
                                ->button()
                                ->requiresConfirmation()
                                ->modalDescription(__('string.associate_with_customer_description'))
                                ->icon('heroicon-o-link')
                                ->color('secondary')

                                ->form([
                                    Select::make('customer_id')
                                        ->label(__('columns.customer'))
                                        ->options(Customer::whereNull('seller_id')->pluck('name','id'))
                                        ->preload()
                                        ->required()
                                        ->searchable()
                                        ->optionsLimit(20)
                                ])
                                ->action(function ($record,$data){

                                    $record->update([
                                        'customer_id' => $data['customer_id']
                                    ]);

                                    $record->associatedAccount->update([
                                        'seller_id' => $record->id
                                    ]);
                                })
                                ->visible(fn($record) => $record->customer_id == null),
                            Actions\Action::make('de_associate_account')
                                ->label(__('string.de_associate_with_customer'))
                                ->button()
                                ->requiresConfirmation()
                                ->modalDescription(__('string.de_associate_with_customer_description'))
                                ->icon('tabler-unlink')
                                ->color('danger')
                                ->action(function ($record){


                                    $record->associatedAccount->update([
                                        'seller_id' => null
                                    ]);

                                    $record->update([
                                        'customer_id' => null
                                    ]);
                                })
                                ->visible(fn($record) => $record->customer_id != null)
                        ])->visible(fn($operation) => $operation == "edit")
                    ]),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('columns.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->searchable()
                    ->label(__('columns.phone'))->extraCellAttributes(['style'=>'direction:ltr !important']),
                TextColumn::make('email')
                    ->label(__('columns.email'))
                    ->searchable()
                    ->sortable(),



                TextColumn::make('country.name')
                    ->label(__('columns.country'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')->label(__('columns.created_date'))->size('xs')
                    ->dateTime('d-m-Y H:i a')
                    ->sortable(),
                IconColumn::make('blocked')
                    ->label(__('columns.block'))
                    ->boolean(),
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

                TextColumn::make('identification_document_status')
                    ->label(__('seller.identification_status'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'approved' => 'success',
                        'rejected' => 'danger',
                    })
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'pending' => __('seller.status.pending'),
                        'approved' => __('seller.status.approved'),
                        'rejected' => __('seller.status.rejected'),
                    }),

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

                Filter::make('identification_document_status')
                    ->label(__('seller.identification_status'))
                    ->query(function (Builder $query, array $data): Builder {
                        return $query->when(
                            $data['value'],
                            fn (Builder $query, $value): Builder => $query->where('identification_document_status', $value),
                        );
                    })
                    ->form([
                        Select::make('value')
                            ->options([
                                'pending' => __('seller.status.pending'),
                                'approved' => __('seller.status.approved'),
                                'rejected' => __('seller.status.rejected'),
                            ])
                            ->placeholder(__('columns.all_status')),
                    ]),

            ])
            ->actions([
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

    public static function getRelations(): array
    {
        return [
            ResponsesRelationManager::class,
            SellerLocationsRelationManager::class,
            SellerProfileServicesRelationManager::class,
            SellerQAsRelationManager::class,
            SellerServicesRelationManager::class,
            SocialMediaRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSellers::route('/'),
            'create' => Pages\CreateSeller::route('/create'),
            'view' => Pages\ViewSeller::route('/{record}'),
            'edit' => Pages\EditSeller::route('/{record}/edit'),
        ];
    }

    public static function getGlobalSearchEloquentQuery(): Builder
    {
        return parent::getGlobalSearchEloquentQuery()->with(['country']);
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email', 'country.name'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        $details = [];

        if ($record->country) {
            $details['Country'] = $record->country->name;
        }

        return $details;
    }
}
