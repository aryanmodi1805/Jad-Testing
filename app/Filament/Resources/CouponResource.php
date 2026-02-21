<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Filament\Resources\CouponResource\RelationManagers;
use App\Models\Package;
use App\Models\Seller;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\MorphToSelect;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use MichaelRubel\Couponables\Models\Contracts\CouponContract;
use MichaelRubel\Couponables\Models\Coupon;

class CouponResource extends Resource
{
    protected static ?string $model = \App\Models\Coupon::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 8 ;
//protected static ?string $tenantOwnershipRelationshipName  = 'coupons';
    protected static bool $isScopedToTenant = true;
    public static function getNavigationGroup(): ?string
    {
        return __('nav.wallet_group');
    }
    public static function getNavigationLabel(): string
    {
        return __('wallet.coupons');
    }

    public function getTitle(): string|Htmlable
    {
        return __('wallet.coupons');
    }

    public static function getModelLabel(): string
    {
        return __('wallet.coupon');
    }

public static function getPluralLabel(): ?string
{
    return __('wallet.coupons');
}

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('code')->required()->string()->label(__('columns.code')),
                Select::make('type')->nullable()->options([
//                    CouponContract::TYPE_FIXED=>CouponContract::TYPE_FIXED,
                    CouponContract::TYPE_PERCENTAGE=> __('wallet.percentage'),
                    CouponContract::TYPE_SUBTRACTION=>__('wallet.subtraction'),
                    ])->label(__('columns.type'),),
                TextInput::make('value')->required()->string()->label(__('columns.value')),
                TextInput::make('limit')->required()->numeric()->label(__('columns.limit')),
                DatePicker::make('expires_at')->nullable()->label(__('wallet.expires_at')),
                Toggle::make('is_enabled')->label(__('columns.active')),
                MorphToSelect::make('redeemer')
                    ->required()
                    ->label(__('wallet.redeemer'))
                    ->types([
                        MorphToSelect\Type::make(Package::class)->label(__('wallet.packages.plural'))
                            ->getOptionLabelFromRecordUsing(fn ( $record) => "{$record->name} ({$record->price} ".getCurrencySample().")")
                             ->titleAttribute('name'),
//                        MorphToSelect\Type::make(Seller::class)->label(__('accounts.sellers.plural'))
//                            ->titleAttribute('name'),

                    ])
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label(__('columns.code')),
                Tables\Columns\TextColumn::make('type')->label(__('columns.type')),
                Tables\Columns\TextColumn::make('value')->label(__('columns.value')),
                Tables\Columns\TextColumn::make('limit')->label(__('columns.limit')),
                Tables\Columns\TextColumn::make('expires_at')->date()->label(__('wallet.expires_at')),
                Tables\Columns\ToggleColumn::make('is_enabled')->label(__('columns.active')),
                Tables\Columns\TextColumn::make('redeemer.name')->label(__('wallet.redeemer')),

            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCoupons::route('/'),
        ];
    }
}
