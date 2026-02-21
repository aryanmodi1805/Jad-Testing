<?php

namespace App\Filament\Customer\Resources;

use App\Enums\RequestStatus;
use App\Enums\ResponseStatus;
use App\Filament\Customer\Resources\BlockReportResource\Pages;
use App\Filament\Customer\Resources\BlockReportResource\RelationManagers;
use App\Models\BlockReport;
use App\Models\Customer;
use App\Models\Request;
use App\Models\Response;
use App\Models\Seller;
use App\Services\RequestService;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;


class CustomerBlockReportResource extends Resource
{
    protected static bool $shouldSkipAuthorization = true;

    protected static ?string $model = BlockReport::class;

    protected static bool $shouldRegisterNavigation = false;

    public static function getPluralLabel(): ?string
    {
        return __('labels.blocked_users');
    }

    public static function getModelLabel(): string
    {
        return __('string.block_reports.singular');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->emptyStateHeading(__('string.block_reports.empty'))
            ->columns([
                Tables\Columns\TextColumn::make('seller')
                    ->label(__('labels.name'))
                    ->getStateUsing(function ($record) {
                        $blocked = $record->blocked;
                        return filled($blocked->company_name) ? $blocked->company_name : $blocked->name;
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('blockReason.name')
                    ->label(__('labels.block_reason'))
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label(__('labels.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([

            ])
            ->actions([
                Tables\Actions\DeleteAction::make()->label(__('string.unblock'))
                    ->before(function(?BlockReport $record) {

                        $reference = $record->reference;

                        if($reference instanceof Response){
                            /*  @var  $reference Response*/
                            /*  @var  $request Request*/
                            $request = $reference->request;

                            if($request->status == RequestStatus::Open){
                                $reference->update([
                                    'status' => ResponseStatus::Pending
                                ]);
                            }
                        }

                        return null;
                    }),

            ])
//            ->bulkActions([
//                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make()->label(__('string.unblock_selected')),
//                ]),
//            ])
            ;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageCustomerBlockReports::route('/'),
        ];
    }
    public static function getEloquentQuery(): Builder
    {

        return parent::getEloquentQuery()->where('blocker_type',Customer::class)->where('blocker_id',Filament::auth()->id())->latest() ;

    }
}


